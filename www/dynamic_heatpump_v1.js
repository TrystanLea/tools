
var app = new Vue({
    el: '#app',
    data: {
        building: {
            internal_gains: 390,
            fabric: [
                { WK: 340, kWhK: 8, T: 13 },
                { WK: 650, kWhK: 8, T: 17 },
                { WK: 1000, kWhK: 2, T: 18 }
            ],
            fabric_WK: 0
        },
        external: {
            mid: 6,
            swing: 0
        },
        heatpump: {
            capacity: 5000,
            min_modulation: 2000,
            system_water_volume: 60, // Litres
            flow_rate: 12, // Litres per minute
            radiatorRatedOutput: 15000,
            radiatorRatedDT: 50
        },
        control: {
            Kp: 10000,
            Ki: 0.1,
            Kd: 0.0
        },
        schedule: [
            { start: "00:00", set_point: 17, flowT: 42 },
            { start: "07:00", set_point: 18, flowT: 42 },
            { start: "16:00", set_point: 19, flowT: 42 },
            { start: "22:00", set_point: 17, flowT: 42 }

        ],
        results: {
            elec_kwh: 0,
            heat_kwh: 0,
            elec_saving_prc: 0,
            heat_saving_prc: 0
        },
        refinements: 3,
        max_room_temp: 0
    },
    methods: {
        simulate: function () {
            for (var i = 0; i < app.refinements; i++) {
                sim();
            }
            plot();
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
                this.schedule.push({ "start": 0, "set_point": 20.0, "flowT": 45.0 });
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

// var hs = 0.1;

ITerm = 0
error = 0

update_fabric_starting_temperatures();
flow_temperature = room;
return_temperature = room;
MWT = room;

app.refinements = 5;
app.simulate();
app.refinements = 3;

function update_fabric_starting_temperatures() {
    t1 = app.building.fabric[0].T;
    t2 = app.building.fabric[1].T;
    room = app.building.fabric[2].T;
}

function sim() {
    roomT_data = [];
    outsideT_data = [];
    flowT_data = [];
    returnT_data = [];
    elec_data = [];
    heat_data = [];

    // Layer 1:
    var u1 = app.building.fabric[0].WK;
    var k1 = 3600000 * app.building.fabric[0].kWhK;
    // Layer 2:
    var u2 = app.building.fabric[1].WK;
    var k2 = 3600000 * app.building.fabric[1].kWhK;
    // Layer 3:
    var u3 = app.building.fabric[2].WK;
    var k3 = 3600000 * app.building.fabric[2].kWhK;

    // Calculate heat loss coefficient
    var sum = 0;
    for (var z in app.building.fabric) {
        sum += (1 / app.building.fabric[z].WK*1);
    }
    app.building.fabric_WK = 1 / sum;

    var start_t1 = t1;

    var timestep = 30;
    var itterations = 3600 * 24 / timestep;

    app.results.elec_kwh = 0;
    app.results.heat_kwh = 0;

    max_flowT = 0;
    setpoint = 0;
    heatpump_heat = 0;
    heatpump_elec = 0;

    var power_to_kwh = timestep / 3600000;

    app.max_room_temp = 0;

    for (var i = 0; i < itterations; i++) {
        let time = i * timestep * 1000;
        let hour = time / 3600000;

        var outside = app.external.mid - Math.cos(2 * Math.PI * (i / itterations)) * app.external.swing * 0.5;

        // Load heating schedule
        for (let j = 0; j < app.schedule.length; j++) {
            let start = time_str_to_hour(app.schedule[j].start);
            if (hour >= start) {
                setpoint = parseFloat(app.schedule[j].set_point);
                max_flowT = parseFloat(app.schedule[j].flowT);
            }
        }

        // 3 term control algorithm
        // Kp = 1400 // Find unstable oscillation point and divide in half.. 
        // Ki = 0.2
        // Kd = 0

        last_error = error
        error = setpoint - room

        // Option: explore control based on flow temp target
        // error = max_flowT - flow_temperature
        delta_error = error - last_error

        PTerm = app.control.Kp * error
        ITerm += error * timestep
        DTerm = delta_error / timestep

        heatpump_heat = PTerm + (app.control.Ki * ITerm) + (app.control.Kd * DTerm)

        // Apply limits
        if (heatpump_heat > app.heatpump.capacity) {
            heatpump_heat = app.heatpump.capacity;
        }
        if (heatpump_heat < 0) {
            heatpump_heat = 0;
        }

        /*
        // Implementation includes system volume
        // Does not work well yet

        MWT += (heatpump_heat * timestep) / (app.heatpump.system_water_volume * 4187)

        system_DT = heatpump_heat / ((app.heatpump.flow_rate/60) * 4187);

        flow_temperature = MWT + system_DT*0.5;
        return_temperature = MWT - system_DT*0.5;

        Delta_T = MWT - room;
        radiator_heat = RatedPower * Math.pow(Delta_T / RatedDeltaT, 1.3);

        MWT -= (radiator_heat * timestep) / (app.heatpump.system_water_volume * 4187)
        */

        // Radiator model
        Delta_T = Math.pow(heatpump_heat / app.heatpump.radiatorRatedOutput, 1 / 1.3) * app.heatpump.radiatorRatedDT;

        system_DT = heatpump_heat / ((app.heatpump.flow_rate / 60) * 4187);

        MWT = room + Delta_T;
        flow_temperature = MWT + system_DT * 0.5;

        // Limit flow temperature
        if (flow_temperature > max_flowT) {
            flow_temperature = max_flowT
        }

        MWT = flow_temperature - system_DT * 0.5;
        return_temperature = MWT - system_DT * 0.5;

        Delta_T = MWT - room;
        heatpump_heat = app.heatpump.radiatorRatedOutput * Math.pow(Delta_T / app.heatpump.radiatorRatedDT, 1.3);
        radiator_heat = heatpump_heat;

        // Simple carnot equation based heat pump model
        let condensor = flow_temperature + 2;
        let evaporator = outside - 6;
        let IdealCOP = (condensor + 273) / ((condensor + 273) - (evaporator + 273));
        let PracticalCOP = 0.5 * IdealCOP;

        if (PracticalCOP > 0) {
            heatpump_elec = heatpump_heat / PracticalCOP;
        } else {
            heatpump_elec = 0;
        }

        // Calculate energy use
        app.results.elec_kwh += heatpump_elec * power_to_kwh;
        app.results.heat_kwh += heatpump_heat * power_to_kwh;

        // Building fabric model

        // 1. Calculate heat fluxes
        h3 = (app.building.internal_gains + radiator_heat) - (u3 * (room - t2));
        h2 = u3 * (room - t2) - u2 * (t2 - t1);
        h1 = u2 * (t2 - t1) - u1 * (t1 - outside);

        // 2. Calculate change in temperature
        room += (h3 * timestep) / k3;
        t2 += (h2 * timestep) / k2;
        t1 += (h1 * timestep) / k1;

        if (room>app.max_room_temp){
            app.max_room_temp = room;
        }

        // Populate time series data arrays for plotting
        roomT_data.push([time, room]);
        outsideT_data.push([time, outside]);
        flowT_data.push([time, flow_temperature]);
        returnT_data.push([time, return_temperature]);
        elec_data.push([time, heatpump_elec]);
        heat_data.push([time, heatpump_heat]);
    }

    calculate_steady_state();

    // Steady state electric demand 



    // Automatic refinement, disabled for now, running simulation 3 times instead.
    // if (Math.abs(start_t1 - t1) > hs * 1.0) sim();
}

function calculate_steady_state(){
    // Calculate steady state comparison
    let dT = app.max_room_temp - app.external.mid;
    let heat_demand = app.building.fabric_WK * dT;
    let heating_demand = heat_demand - app.building.internal_gains
    let heating_demand_kwh = heating_demand * 0.024;

    // Steady state flow temperature
    dT = Math.pow(heating_demand / app.heatpump.radiatorRatedOutput, 1 / 1.3) * app.heatpump.radiatorRatedDT;
    let MWT = app.max_room_temp + dT;
    let system_DT = heating_demand / ((app.heatpump.flow_rate / 60) * 4187);
    let flow_temperature = MWT + system_DT * 0.5;

    // Steady state COP
    let condensor = flow_temperature + 2;
    let evaporator = app.external.mid - 6;
    let IdealCOP = (condensor + 273) / ((condensor + 273) - (evaporator + 273));
    let PracticalCOP = 0.5 * IdealCOP;

    let heatpump_elec_kwh = heating_demand_kwh / PracticalCOP;

    app.results.elec_saving_prc = 100* (1-(app.results.elec_kwh / heatpump_elec_kwh))
    app.results.heat_saving_prc = 100* (1-(app.results.heat_kwh / heating_demand_kwh))
}

function plot() {
    var series = [
        { label: "Heat", data: heat_data, color: 0, yaxis: 3, lines: { show: true, fill: true } },
        { label: "Elec", data: elec_data, color: 1, yaxis: 3, lines: { show: true, fill: true } },
        { label: "FlowT", data: flowT_data, color: 2, yaxis: 2, lines: { show: true, fill: false } },
        { label: "ReturnT", data: returnT_data, color: 3, yaxis: 2, lines: { show: true, fill: false } },
        { label: "RoomT", data: roomT_data, color: "#000", yaxis: 1, lines: { show: true, fill: false } },
        { label: "OutsideT", data: outsideT_data, color: "#0000cc", yaxis: 1, lines: { show: true, fill: false } }

    ];

    var options = {
        grid: { show: true, hoverable: true },
        xaxis: { mode: 'time' },
        yaxes: [{}, { min: 1.5 }],
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
            if (item.series.label == "Elec") { unit = "W"; dp = 0; }
            else if (item.series.label == "Heat") { unit = "W"; dp = 0; }
            else if (item.series.label == "FlowT") { unit = "°C"; dp = 1; }
            else if (item.series.label == "ReturnT") { unit = "°C"; dp = 1; }
            else if (item.series.label == "RoomT") { unit = "°C"; dp = 1; }

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

$(window).resize(function () {
    $('#graph').width($('#graph_bound').width());
    plot();
});
