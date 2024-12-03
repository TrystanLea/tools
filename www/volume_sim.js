var app = new Vue({
    el: '#app',
    data: {
        hours: 1,
        roomT: 20,
        minimum_heat_output: 2000,
        heat_demand: 1000,
        system_volume: 100,
        radiatorRatedOutput: 15000,
        radiatorRatedDT: 50,
        max_room_temp: 0,
        starts_per_hour: 3,
        cycle_DT: 0
    },
    methods: {
        simulate: function () {
            sim_count = 0;
            sim();
            plot();
        }
    },
    filters: {
        toFixed: function (val, dp) {
            if (isNaN(val)) {
                return val;
            } else {
                return val.toFixed(dp)
            }
        }
    }
});



$('#graph').width($('#graph_bound').width()).height($('#graph_bound').height());

room = app.roomT;
MWT = room;
sim_count = 0;

app.simulate();

function sim() {
    sim_count++;

    MWT_data = [];
    heatpump_heat_data = [];
    radiator_heat_data = [];

    var timestep = 10;
    var itterations = 3600 * app.hours / timestep;

    // cycling control
    var duty_cycle = app.heat_demand / app.minimum_heat_output;
    if (duty_cycle > 1) duty_cycle = 1;

    var period = 3600 / app.starts_per_hour;
    var on_time = period * duty_cycle;
    var off_time = period - on_time;

    var rad_heat_sum = 0;
    
    for (var i = 0; i < itterations; i++) {
        let time = i * timestep;
        let hour = time / 3600;
        hour = hour % 24;

        // Is the heat pump running
        if (time % period < on_time) {

            if (app.heat_demand > app.minimum_heat_output) {
                heatpump_heat = app.heat_demand;
            } else {
                heatpump_heat = app.minimum_heat_output;
            }

        } else {
            heatpump_heat = 0;
        }
        

        // 1. Heat added to system volume from heat pump
        MWT += (heatpump_heat * timestep) / (app.system_volume * 4187)

        // 2. Calculate radiator output based on Room temp and MWT
        Delta_T = MWT - room;
        radiator_heat = app.radiatorRatedOutput * Math.pow(Delta_T / app.radiatorRatedDT, 1.3);
        rad_heat_sum += radiator_heat;

        // 3. Subtract this heat output from MWT
        MWT -= (radiator_heat * timestep) / (app.system_volume * 4187)
        
        // Populate time series data arrays for plotting
        let timems = time*1000;
        MWT_data.push([timems, MWT]);
        heatpump_heat_data.push([timems, heatpump_heat]);
        radiator_heat_data.push([timems, radiator_heat]);
    }


    mean_rad_heat = rad_heat_sum / itterations;


    var diff_rad_heat = Math.abs(app.heat_demand - mean_rad_heat);
    if (diff_rad_heat > 1 && sim_count < 20) {
        sim();
    }


    console.log("Mean radiator heat output: " + mean_rad_heat.toFixed(0) + "W (sim_count: " + sim_count + ")"); 
}


function plot() {
    var series = [
        // orange #f90
        { label: "Heatpump heat", data: heatpump_heat_data, color: 0, yaxis: 1, lines: { show: true, fill: true } },
        { label: "Emitter heat", data: radiator_heat_data, color: "#f90", yaxis: 1, lines: { show: true, fill: true } },
        { label: "MWT", data: MWT_data, color: "#000", yaxis: 2, lines: { show: true, fill: false } }

    ];

    // Get minimum and maximum MWt values
    let min = MWT_data[0][1];
    let max = MWT_data[0][1];
    for (let i = 0; i < MWT_data.length; i++) {
        if (MWT_data[i][1] < min) min = MWT_data[i][1];
        if (MWT_data[i][1] > max) max = MWT_data[i][1];
    }

    app.cycle_DT = (max - min).toFixed(2);

    var options = {
        grid: { show: true, hoverable: true },
        xaxis: { mode: 'time' },
        yaxes: [{}, { min: min-1, max: max+1 }],
        selection: { mode: "xy" }
    };

    var plot = $.plot($('#graph'), series, options);
}

var previousPoint = false;

// flot tooltip
$('#graph').bind("plothover", function (event, pos, item) {
    if (item) {
        var z = item.dataIndex;

        if (previousPoint != item.datapoint) {
            previousPoint = item.datapoint;

            $("#tooltip").remove();

            let unit = "";
            let dp = 0;/*
            if (item.series.label == "Elec") { unit = "W"; dp = 0; }
            else if (item.series.label == "Heat") { unit = "W"; dp = 0; }
            else if (item.series.label == "FlowT") { unit = "°C"; dp = 1; }
            else if (item.series.label == "ReturnT") { unit = "°C"; dp = 1; }
            else if (item.series.label == "RoomT") { unit = "°C"; dp = 1; }*/

            if (item.series.label == "Heatpump heat") { unit = "W"; dp = 0; }
            else if (item.series.label == "Emitter heat") { unit = "W"; dp = 0; }
            else if (item.series.label == "MWT") { unit = "°C"; dp = 1; }

            var itemTime = hour_to_time_str(item.datapoint[0] / 3600000);
            var itemValue = item.datapoint[1];
            tooltip(item.pageX, item.pageY, item.series.label + ": " + (item.datapoint[1]).toFixed(dp) + unit + "<br>" + itemTime, "#fff", "#000");

        }
    } else $("#tooltip").remove();
});

function tooltip(x, y, contents, bgColour, borderColour = "rgb(255, 221, 221)") {
    var offset = 10;
    var elem = $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        color: "#000",
        display: 'none',
        'font-weight': 'bold',
        border: '1px solid ' + borderColour,
        padding: '2px',
        'background-color': bgColour,
        opacity: '0.8',
        'text-align': 'left'
    }).appendTo("body").fadeIn(200);

    var elemY = y - elem.height() - offset;
    var elemX = x - elem.width() - offset;
    if (elemY < 0) { elemY = 0; }
    if (elemX < 0) { elemX = 0; }
    elem.css({
        top: elemY,
        left: elemX
    });
}

function hour_to_time_str(hour_min) {
    let hour = Math.floor(hour_min);
    let min = Math.round((hour_min - hour) * 60);
    if (hour < 10) hour = "0" + hour;
    if (min < 10) min = "0" + min;
    return hour + ":" + min;
}

$(window).resize(function () {
    $('#graph').width($('#graph_bound').width());
    plot();
});