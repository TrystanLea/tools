
var app = new Vue({
    el: '#app',
    data: {
        heat_loss: 3.0,                 // kW
        heat_pump_capacity: 5.0,        // kW
        minimum_modulation: 0.4,        // %
        rated_emitter_output_dt50: 15,  // kW
        room_temperature: 20,           // °C
        design_outsideT: -3,            // °C
        systemDT: 5,                    // °C

        limit_curve: true,
        show_curve_to_zero: false,

        design_flowT: 0,                // °C
        minimum_flowT: 0,               // °C
        outsideT_cutoff: 0              // °C
    },
    methods: {
        update: function () {
            app.model();
        },

        model: function () {
            // Delta_T = (Heat_output / Rated_Heat_Output)^(1/1.3) x Rated_Delta_T
            let DT = Math.pow((app.heat_loss / app.rated_emitter_output_dt50), 1 / 1.3) * 50;
            let MWT = app.room_temperature + DT;
            app.design_flowT = MWT + (app.systemDT * 0.5);

            let minimum_heat_output = app.heat_pump_capacity * app.minimum_modulation;
            DT = Math.pow((minimum_heat_output / app.rated_emitter_output_dt50), 1 / 1.3) * 50;
            MWT = app.room_temperature + DT;
            app.minimum_flowT = MWT + (app.systemDT * 0.5);

            let HTC = app.heat_loss / (app.room_temperature - app.design_outsideT);

            app.outsideT_cutoff = app.room_temperature - (minimum_heat_output / HTC);

            // Graph

            var data = [];

            let min_graph_temp = Math.floor(app.design_outsideT - 2);
            let max_graph_temp = Math.ceil(app.outsideT_cutoff + 2);

            if (app.show_curve_to_zero) {
                max_graph_temp = Math.floor(app.room_temperature);
            }

            for (let outsideT = min_graph_temp; outsideT <= max_graph_temp; outsideT += 0.5) {

                let heat_demand = HTC * (app.room_temperature - outsideT);
                let DT = Math.pow((heat_demand / app.rated_emitter_output_dt50), 1 / 1.3) * 50;
                let MWT = app.room_temperature + DT;
                let flowT = MWT + (app.systemDT * 0.5);

                if (app.limit_curve) {
                    if (flowT < app.minimum_flowT) {
                        flowT = app.minimum_flowT;
                    }

                    if (flowT > app.design_flowT) {
                        flowT = app.design_flowT;
                    }
                }



                data.push([outsideT, flowT]);
            }

            var markings = [
                { color: "#f6f6f6", xaxis: { from: app.outsideT_cutoff } },
                { color: "#f6f6f6", xaxis: { to: app.design_outsideT } },
                { color: "#000", lineWidth: 1, xaxis: { from: app.design_outsideT, to: app.design_outsideT } },
                { color: "#000", lineWidth: 1, xaxis: { from: app.outsideT_cutoff, to: app.outsideT_cutoff } }
            ];


            $.plot("#graph", [data], {
                // bar graph
                series: {
                    lines: {
                        show: true,
                        fill: false
                    },
                    points: {
                        show: true
                    }
                },
                xaxis: {
                },
                yaxis: {
                },
                selection: {
                    mode: "x"
                },
                grid: {
                    hoverable: true,
                    clickable: true,
                    markings: markings
                }
            });

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

app.model();