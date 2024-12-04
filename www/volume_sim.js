var app = new Vue({
    el: '#app',
    data: {
        cycles_to_simulate: 3,
        hours: 0,
        roomT: 20,
        minimum_heat_output: 2000,
        heat_demand: 1000,
        system_volume: 75,
        radiatorRatedOutput: 15000,
        radiatorRatedDT: 50,
        max_room_temp: 0,
        starts_per_hour: 1,
        return_DT: 0,
        mwt_DT: 0,
        system_DT: 3
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
flowT = room;
returnT = room;
sim_count = 0;

app.simulate();

function sim() {
    sim_count++;

    MWT_data = [];
    flowT_data = [];
    returnT_data = [];
    heatpump_heat_data = [];
    radiator_heat_data = [];

    app.hours = app.cycles_to_simulate * 1 / app.starts_per_hour;

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
        returnT += (heatpump_heat * timestep) / (app.system_volume * 4187)

        DT = 0;
        if (heatpump_heat > 0) {
            DT = app.system_DT;
        }

        flowT = returnT + DT;

        MWT = (flowT + returnT) / 2;

        // 2. Calculate radiator output based on Room temp and MWT
        Delta_T = MWT - room;
        radiator_heat = app.radiatorRatedOutput * Math.pow(Delta_T / app.radiatorRatedDT, 1.3);
        rad_heat_sum += radiator_heat;

        // 3. Subtract this heat output from MWT
        returnT -= (radiator_heat * timestep) / (app.system_volume * 4187)
        MWT = (flowT + returnT) / 2;
        
        // Populate time series data arrays for plotting
        let timems = time*1000;
        flowT_data.push([timems, flowT]);
        returnT_data.push([timems, returnT]);
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
        { label: "FlowT", data: flowT_data, color: 2, yaxis: 2, lines: { show: true, fill: false } },
        { label: "ReturnT", data: returnT_data, color: 3, yaxis: 2, lines: { show: true, fill: false } },
    ];

    var return_range = get_min_max(returnT_data);
    var flow_range = get_min_max(flowT_data);
    var mwt_range = get_min_max(MWT_data);

    app.return_DT = (return_range.max - return_range.min).toFixed(2);
    app.mwt_DT = (mwt_range.max - mwt_range.min).toFixed(2);

    var options = {
        grid: { show: true, hoverable: true },
        xaxis: { mode: 'time' },
        yaxes: [{}, { min: return_range.min-5, max: flow_range.max+1 }],
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
            let dp = 0;

            if (item.series.label == "Heatpump heat") { unit = "W"; dp = 0; }
            else if (item.series.label == "Emitter heat") { unit = "W"; dp = 0; }
            else if (item.series.label == "MWT") { unit = "°C"; dp = 1; }
            else if (item.series.label == "FlowT") { unit = "°C"; dp = 1; }
            else if (item.series.label == "ReturnT") { unit = "°C"; dp = 1; }

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

function get_min_max(data) {
    let min = data[0][1];
    let max = data[0][1];
    for (let i = 0; i < data.length; i++) {
        if (data[i][1] < min) min = data[i][1];
        if (data[i][1] > max) max = data[i][1];
    }
    return { min: min, max: max };
} 

$(window).resize(function () {
    $('#graph').width($('#graph_bound').width());
    plot();
});
