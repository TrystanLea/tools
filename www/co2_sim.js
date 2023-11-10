var app = new Vue({
    el: '#app',
    data: {
        ambient_co2: 420,
        exp_baseline_co2: 420,
        building: {
            volume: 75,
            air_change_rate: 0.35
        },
        schedule: [
            { start: "00:00", co2_production: 0 },
            { start: "10:00", co2_production: 36 },
            { start: "18:00", co2_production: 0 }

        ],
        results: {
            mean: 0,
            min: 0,
            max: 0
        },
        selection_air_change_rate: '?',
        refinements: 3
    },
    methods: {
        simulate: function () {
            for (var i = 0; i < app.refinements; i++) {
                sim();
            }
            plot();
        },
        volume_change: function () {
            room_volume_litres = app.building.volume * 1000;
            co2_ppm = 5000;
            co2_litres = room_volume_litres * (co2_ppm / 1000000);

            this.simulate();
        },
        update_exp_fit: function () {
            plot_exp_fit();
        },
        add_space: function () {
            if (this.schedule.length > 0) {
                let last = JSON.parse(JSON.stringify(this.schedule[this.schedule.length - 1]))
                let hour = time_str_to_hour(last.start);
                hour += 1;
                if (hour > 23) hour = 23;
                last.start = hour_to_time_str(hour);
                this.schedule.push(last);
            } else {
                this.schedule.push({ "start": 0, "co2_production": 20.0 });
            }
            this.simulate();
        },
        delete_space: function (index) {
            this.schedule.splice(index, 1);
            this.simulate();
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

function time_str_to_hour(time_str) {
    let hourmin = time_str.split(":");
    let hour = parseInt(hourmin[0]) + parseInt(hourmin[1]) / 60;
    return hour;
}

function hour_to_time_str(hour_min) {
    let hour = Math.floor(hour_min);
    let min = Math.round((hour_min - hour) * 60);
    if (hour < 10) hour = "0" + hour;
    if (min < 10) min = "0" + min;
    return hour + ":" + min;
}

$('#graph').width($('#graph_bound').width()).height($('#graph_bound').height());

var room_volume_litres = app.building.volume * 1000;
var co2_ppm = 5000;
var co2_litres = room_volume_litres * (co2_ppm / 1000000);

var timestep = 30;

var selection = {
    start_time: null,
    end_time: null,
    start_val: null,
    end_val: null
}

app.refinements = 5;
app.simulate();
app.refinements = 3;

function sim() {
    co2_data = [];
    exp_decay_data = [];

    
    var itterations = 3600 * 24 / timestep;

    var co2_production = 0;

    var litres_ambient_air_per_second = room_volume_litres * (app.building.air_change_rate / 3600);

    var sum = 0;
    var min = null;
    var max = null;
    
    for (var i = 0; i < itterations; i++) {
        let time = i * timestep;
        let hour = time / 3600;

        // Load heating schedule
        for (let j = 0; j < app.schedule.length; j++) {
            let start = time_str_to_hour(app.schedule[j].start);
            if (hour >= start) {
                co2_production = parseFloat(app.schedule[j].co2_production);
            }
        }

        let co2_addition = (co2_production / 3600) * timestep;
        co2_litres += co2_addition;

        // Calculate CO2 lost
        let co2_ambient_in = litres_ambient_air_per_second * timestep *  (app.ambient_co2 / 1000000);
        let co2_room_out = litres_ambient_air_per_second * timestep * (co2_litres / room_volume_litres);
        let co2_losses =  co2_room_out - co2_ambient_in;
        co2_litres -= co2_losses;

        co2_ppm = (co2_litres / room_volume_litres) * 1000000;

        // Populate time series data arrays for plotting
        let timems = time*1000;
        co2_data.push([timems, co2_ppm]);

        sum += co2_ppm;

        if (min == null || co2_ppm < min) min = co2_ppm;
        if (max == null || co2_ppm > max) max = co2_ppm;
    }

    app.results.mean = sum / itterations;
    app.results.min = min;
    app.results.max = max;
}

function plot() {
    var series = [
        { label: "CO2", data: co2_data, color: 0, yaxis: 1, lines: { show: true, fill: false } },
        { label: "Exp fit", data: exp_decay_data, color: "#000", yaxis: 1, lines: { show: true, fill: false } }
    ];

    var options = {
        grid: { show: true, hoverable: true },
        xaxis: { mode: 'time' },
        yaxes: [{}],
        selection: { mode: "x" }
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
            if (item.series.label == "CO2") { unit = "ppm"; dp = 0; }

            var itemTime = hour_to_time_str(item.datapoint[0] / 3600000);
            var itemValue = item.datapoint[1];
            tooltip(item.pageX, item.pageY, item.series.label + ": " + (item.datapoint[1]).toFixed(dp) + unit + "<br>" + itemTime, "#fff", "#000");

        }
    } else $("#tooltip").remove();
});

// plot selection
$("#graph").bind("plotselected", function (event, ranges) {

    selection.start_time = Math.round(ranges.xaxis.from*0.001/timestep)*timestep;
    selection.end_time = Math.round(ranges.xaxis.to*0.001/timestep)*timestep;

    // get values at start and end of selection
    selection.start_val = null;
    selection.end_val = null;
    for (var i = 0; i < co2_data.length; i++) {
        if (co2_data[i][0]*0.001 == selection.start_time) selection.start_val = co2_data[i][1];
        if (co2_data[i][0]*0.001 == selection.end_time) selection.end_val = co2_data[i][1];
    }

    if (selection.start_val == null || selection.end_val == null) {
        alert("Error: could not find data points for selected range");
        return;
    }

    if (selection.end_val < selection.start_val) {
        
        plot_exp_fit();

    } else {
        app.selection_air_change_rate = "?";
    }
});

function plot_exp_fit() {

    var time_change = selection.end_time - selection.start_time;
    var co2_start_minus_ambient = selection.start_val - app.exp_baseline_co2;
    var co2_end_minus_ambient = selection.end_val - app.exp_baseline_co2;

    app.selection_air_change_rate = ((-1*Math.log(co2_end_minus_ambient / co2_start_minus_ambient))/time_change)*3600;  

    exp_decay_data = [];
    for (var time = selection.start_time; time <= selection.end_time; time += timestep) {
        var co2 = (selection.start_val - app.exp_baseline_co2)*Math.exp(-1*(app.selection_air_change_rate/3600)*(time-selection.start_time))+app.exp_baseline_co2;
        exp_decay_data.push([time*1000,co2]);
    }
    plot();
}

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

$(window).resize(function () {
    $('#graph').width($('#graph_bound').width());
    plot();
});
