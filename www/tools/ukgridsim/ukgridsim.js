// Start by loading hourly average outside temperature data from emoncms.org API
// Create basic Vue outline
var series = [];

// Used for input data normalisation
var input_solar_data_GWh = 0;
var input_wind_data_GWh = 0;
var input_demand_data_GWh = 0;
var input_heatpump_data_GWh = 0;

// Power series
var solar_data = [];
var wind_data = [];
var nuclear_data = [];
var trad_demand_data = [];
var demand_data = [];
var store1_soc_data = [];
var store2_soc_data = [];
var store2_discharge_data = [];

var month_timestamps = [];

var app = new Vue({
    el: '#app',
    data: {

        // demand
        standard_demand_TWh: 0,
        heatpump_households: 0,

        // solar generation
        solar_prc_of_demand: 15,
        solar_GWh_per_GWp: 870,
        solar_GWp: 0,
        solar_GWh: 0,

        // wind generation
        wind_prc_of_demand: 95,
        wind_cap_factor: 40,
        wind_GWp: 0,
        wind_GWh: 0,

        // nuclear generation
        nuclear_prc_of_demand: 10,
        nuclear_cap_factor: 90,
        nuclear_GWp: 0,
        nuclear_GWh: 0,

        supply_GWh: 0,
        demand_GWh: 0,

        demand_met: "---",

        balance: {
            before_store1: 0,
            after_store1: 0,
            after_store2: 0,
            surplus: 0,
            unmet: 0
        },

        max_curtailement: 0,

        store1: {
            capacity: 300,
            soc_start: 0,
            charge_GWh: 0,
            discharge_GWh: 0,
            charge_max: 100,
            discharge_max: 100,
            round_trip_efficiency: 80
        },

        store2: {
            charge_max: 20.0,
            charge_efficiency: 80,
            discharge_max: 38,
            discharge_efficiency: 50,
            capacity: 8000,
            starting_soc: 8000,
            charge_GWh: 0,
            discharge_GWh: 0,
            cycles: 0,
            max_charge: 0,
            max_discharge: 0
        },

        auto_optimise: false,
        show_peak_shaving_balance: false,
        max_peak_shaving_deficit: 0,

        interval: 900,
        run_count: 0,

        view: "power" // or "power"
    },
    methods: {
        update: function () {
            console.log("---- Update ----");
            app.run_count = 0;
            app.model();
        },
        model: function () {
            app.run_count++;

            let standard_demand_scaler = app.standard_demand_TWh / (input_demand_data_GWh * 0.001);

            // Demand
            app.demand_GWh = (input_demand_data_GWh * standard_demand_scaler) + input_heatpump_data_GWh;

            // Solar generation
            app.solar_GWh = (app.solar_prc_of_demand / 100) * app.demand_GWh;
            app.solar_GWp = app.solar_GWh / app.solar_GWh_per_GWp;

            // Wind generation
            app.wind_GWh = (app.wind_prc_of_demand / 100) * app.demand_GWh;
            let wind_average_power = app.wind_GWh / (365 * 24);
            app.wind_GWp = wind_average_power / (app.wind_cap_factor / 100);

            // Nuclear generation
            app.nuclear_GWh = (app.nuclear_prc_of_demand / 100) * app.demand_GWh;
            let nuclear_average_power = app.nuclear_GWh / (365 * 24);
            app.nuclear_GWp = nuclear_average_power / (app.nuclear_cap_factor / 100);

            // reset power series
            solar_data = [];
            wind_data = [];
            nuclear_data = [];
            demand_data = [];
            trad_demand_data = [];
            store1_soc_data = [];
            store2_soc_data = [];
            demand_plus_store_charge_data = [];
            store2_discharge_data = [];

            let solar_GWh = 0;
            let wind_GWh = 0;
            let nuclear_GWh = 0;
            let supply_GWh = 0;
            let demand_GWh = 0;

            let deficit_before_store1_GWh = 0;
            let deficit_after_store1_GWh = 0;
            let deficit_after_store2_GWh = 0;

            let balance_surplus = 0;
            let balance_unmet = 0;

            let peak_shaving_balance = 0;

            let store1_charge_GWh = 0;
            let store1_discharge_GWh = 0;
            let store1_max_charge = 0;
            let store1_max_discharge = 0;

            let store2_charge_GWh = 0;
            let store2_discharge_GWh = 0;
            let store2_max_charge = 0;
            let store2_max_discharge = 0;
            let store2_max_level = 0;
            let store2_min_level = 100000;

            let max_curtailement = 0;

            if (app.auto_optimise) {
                app.store1.charge_max = 1000;
                app.store1.discharge_max = 1000;
                app.store2.charge_max = 1000;
                app.store2.discharge_max = 1000;
                // app.store2.capacity = 100000;
                // app.store2.starting_soc = 5000;
                // app.store2.soc = app.store2.starting_soc;                
            }

            let max_peak_shaving_deficit = 0;

            // Setup store1
            let store1_soc = app.store1.soc_start;
            let store1_charge_efficiency = 1 - ((1 - app.store1.round_trip_efficiency * 0.01) / 2);
            let store1_discharge_efficiency = 1 - ((1 - app.store1.round_trip_efficiency * 0.01) / 2);
            app.store1.discharge_max = app.store1.charge_max;

            // Setup store2
            let store2_soc = app.store2.starting_soc;
            let store2_charge_efficiency = app.store2.charge_efficiency * 0.01;
            let store2_discharge_efficiency = app.store2.discharge_efficiency * 0.01;

            let power_to_GWh = app.interval / 3600;

            // Normalisation factors
            let wind_normalisation_factor = app.wind_GWh / input_wind_data_GWh;
            let solar_normalisation_factor = app.solar_GWh_per_GWp / input_solar_data_GWh;

            for (var i = 0; i < series[0].data.length; i++) {

                // Solar generation
                let solarpv = series[2].data[i][1] * solar_normalisation_factor * app.solar_GWp;
                let wind = series[1].data[i][1] * wind_normalisation_factor;
                let nuclear = app.nuclear_GWp * app.nuclear_cap_factor / 100;

                // Demand
                let trad_demand = series[0].data[i][1] * 0.001 * standard_demand_scaler; // MW to GW
                let heatpump = series[3].data[i][1] * 0.001 * app.heatpump_households;

                let demand = trad_demand + heatpump;

                if (demand < 0) {
                    demand = 0;
                }

                var supply = solarpv + wind + nuclear;

                solar_GWh += solarpv * power_to_GWh;
                wind_GWh += wind * power_to_GWh;
                nuclear_GWh += nuclear * power_to_GWh;

                supply_GWh += supply * power_to_GWh;
                demand_GWh += demand * power_to_GWh;

                // Balance
                var balance = supply - demand;

                // Record deficit before store1 storage
                if (balance < 0) {
                    let deficit_before_store1 = -balance;
                    deficit_before_store1_GWh += deficit_before_store1 * power_to_GWh;
                }

                // store1
                if (app.store1.capacity > 0) {
                    if (balance > 0) {

                        // Charge store1
                        let charge = balance;
                        if (charge > app.store1.charge_max) {
                            charge = app.store1.charge_max;
                        }
                        let charge_after_loss = charge * store1_charge_efficiency;
                        let soc_inc = charge_after_loss * power_to_GWh;
                        // Limit charge to store1 capacity
                        if (store1_soc + soc_inc > app.store1.capacity) {
                            soc_inc = app.store1.capacity - store1_soc;
                            charge_after_loss = soc_inc * (1 / power_to_GWh);
                            charge = charge_after_loss / store1_charge_efficiency;
                        }
                        if (charge > store1_max_charge) {
                            store1_max_charge = charge;
                        }
                        store1_soc += soc_inc;
                        balance -= charge;
                        store1_charge_GWh += charge * power_to_GWh;
                    } else {
                        // Discharge store1
                        let discharge = -balance;
                        if (discharge > app.store1.discharge_max * 1000) {
                            discharge = app.store1.discharge_max * 1000;
                        }
                        let discharge_before_loss = discharge / store1_discharge_efficiency;
                        let soc_dec = discharge_before_loss * power_to_GWh;
                        // Limit discharge to store1 SOC
                        if (store1_soc - soc_dec < 0) {
                            soc_dec = store1_soc;
                            discharge_before_loss = soc_dec * (1 / power_to_GWh);
                            discharge = discharge_before_loss * store1_discharge_efficiency;
                        }
                        if (discharge > store1_max_discharge) {
                            store1_max_discharge = discharge;
                        }
                        store1_soc -= soc_dec;
                        balance += discharge;
                        store1_discharge_GWh += discharge * power_to_GWh;
                    }
                }

                // Record deficit after store1 storage
                if (balance < 0) {
                    let deficit_after_store1 = -balance;
                    deficit_after_store1_GWh += deficit_after_store1 * power_to_GWh;
                }

                let store2_charge = 0;
                let store2_discharge = 0;

                // Store 2 (hydrogen, e-methanol LDES)
                if (balance > 0) {
                    // Charge store
                    let charge = balance;
                    if (charge > app.store2.charge_max) {
                        charge = app.store2.charge_max;
                    }
                    let charge_after_loss = charge * store2_charge_efficiency;
                    let soc_inc = charge_after_loss * power_to_GWh;
                    // Limit charge to store capacity
                    if (store2_soc + soc_inc > app.store2.capacity) {
                        soc_inc = app.store2.capacity - store2_soc;
                        charge_after_loss = soc_inc * (1 / power_to_GWh);
                        charge = charge_after_loss / store2_charge_efficiency;
                    }
                    if (charge > store2_max_charge) {
                        store2_max_charge = charge;
                    }
                    store2_soc += soc_inc;
                    balance -= charge;
                    store2_charge_GWh += charge * power_to_GWh;
                    store2_charge = charge;
                } else {
                    // Discharge store
                    let discharge = -balance;
                    if (discharge > app.store2.discharge_max) {
                        discharge = app.store2.discharge_max;
                    }
                    // peak_shaving_balance -= (-balance - app.store2.discharge_max) * power_to_GWh;

                    let discharge_before_loss = discharge / store2_discharge_efficiency;
                    let soc_dec = discharge_before_loss * power_to_GWh;
                    // Limit discharge to store SOC
                    if (store2_soc - soc_dec < 0) {
                        soc_dec = store2_soc;
                        discharge_before_loss = soc_dec * (1 / power_to_GWh);
                        discharge = discharge_before_loss * store2_discharge_efficiency;
                    }
                    if (discharge > store2_max_discharge) {
                        store2_max_discharge = discharge;
                    }
                    store2_soc -= soc_dec;
                    balance += discharge;
                    store2_discharge_GWh += discharge * power_to_GWh;
                    store2_discharge = discharge;
                }

                // Record max and min store level
                if (store2_soc > store2_max_level) {
                    store2_max_level = store2_soc;
                }
                if (store2_soc < store2_min_level) {
                    store2_min_level = store2_soc;
                }

                if (peak_shaving_balance > 0) {
                    peak_shaving_balance = 0;
                }

                if (-peak_shaving_balance > max_peak_shaving_deficit) {
                    max_peak_shaving_deficit = -peak_shaving_balance;
                }

                // Record deficit after store2 storage
                if (balance < 0) {
                    let deficit_after_store2 = -balance;
                    deficit_after_store2_GWh += deficit_after_store2 * power_to_GWh;
                } else {
                    balance_surplus += balance * power_to_GWh;
                    if (balance > max_curtailement) {
                        max_curtailement = balance;
                    }
                }

                let time = series[0].data[i][0];
                solar_data.push([time, solarpv]);
                wind_data.push([time, wind]);
                nuclear_data.push([time, nuclear]);
                demand_data.push([time, demand]);
                trad_demand_data.push([time, trad_demand]);
                store1_soc_data.push([time, store1_soc]);
                store2_soc_data.push([time, store2_soc]);
                store2_discharge_data.push([time, store2_discharge]);
                demand_plus_store_charge_data.push([time, demand + store2_charge]);

            }

            if (app.auto_optimise) {
                if (store1_max_charge < app.store1.charge_max) {
                    app.store1.charge_max = 1 * (store1_max_charge).toFixed(2);
                }
                if (store2_max_charge < app.store2.charge_max) {
                    app.store2.charge_max = 1 * (store2_max_charge).toFixed(2);
                }
                if (store1_max_discharge < app.store1.discharge_max) {
                    app.store1.discharge_max = 1 * (store1_max_discharge).toFixed(2);
                }
                if (store2_max_discharge < app.store2.discharge_max) {
                    app.store2.discharge_max = 1 * (store2_max_discharge).toFixed(2);
                }
                // let store_diff = store2_max_level - store2_min_level;
                // app.store2.capacity = 1*(store_diff*1.1).toFixed(0);
                // app.store2.starting_soc = 1*(store_diff*0.05).toFixed(0);
            }

            app.store1.charge_CF = store1_charge_GWh / (app.store1.charge_max * 24 * 365);
            app.store2.charge_CF = store2_charge_GWh / (app.store2.charge_max * 24 * 365);
            app.store1.discharge_CF = store1_discharge_GWh / (app.store1.discharge_max * 24 * 365);
            app.store2.discharge_CF = store2_discharge_GWh / (app.store2.discharge_max * 24 * 365);

            app.balance.before_store1 = (demand_GWh - deficit_before_store1_GWh) / demand_GWh;
            app.balance.after_store1 = (demand_GWh - deficit_after_store1_GWh) / demand_GWh;
            app.balance.after_store2 = (demand_GWh - deficit_after_store2_GWh) / demand_GWh;
            app.balance.unmet = deficit_after_store2_GWh;
            app.balance.surplus = balance_surplus;
            app.max_curtailement = max_curtailement;

            app.store1.cycles = 0.5 * (store1_charge_GWh + store1_discharge_GWh) / app.store1.capacity;
            app.store2.cycles = 0.5 * (store2_charge_GWh + store2_discharge_GWh) / app.store2.capacity;

            // Copy over to vue (faster than using vue reactive data during model run)
            app.solar_GWh = solar_GWh;
            app.wind_GWh = wind_GWh;
            app.nuclear_GWh = nuclear_GWh;
            app.supply_GWh = supply_GWh;
            app.demand_GWh = demand_GWh;



            console.log("Run count: " + app.run_count);
            console.log("Annual wind: " + wind_GWh.toFixed(0) + " GWh");
            console.log("Annual solar: " + solar_GWh.toFixed(0) + " GWh");
            console.log("Annual nuclear: " + nuclear_GWh.toFixed(0) + " GWh");

            app.store1.soc_start = store1_soc;
            if (store1_soc > 10 && app.run_count < 3) {
                console.log("Re-running model with store1 SOC start: " + app.store1.soc_start.toFixed(2) + " GWh");
                app.model();
            }

            app.store2.starting_soc = store2_soc;
            if (store2_soc > 10 && app.run_count < 3) {
                console.log("Re-running model with store2 SOC start: " + app.store2.starting_soc.toFixed(2) + " GWh");
                app.model();
            }

            app.draw_power_view();
        },
        normalise: function () {
            // Normalise solar data to match solar_GWp
            input_solar_data_GWh = 0;
            input_wind_data_GWh = 0;
            input_demand_data_GWh = 0;
            input_heatpump_data_GWh = 0;

            let power_to_GWh = app.interval / 3600;

            for (var i = 0; i < series[0].data.length; i++) {
                let demand = series[0].data[i][1] * 0.001; // MW to GW
                let wind = series[1].data[i][1];
                let solar = series[2].data[i][1];
                let heatpump = series[3].data[i][1] * 0.001 * app.heatpump_households;

                input_demand_data_GWh += demand * power_to_GWh;
                input_wind_data_GWh += wind * power_to_GWh;
                input_solar_data_GWh += solar * power_to_GWh;
                input_heatpump_data_GWh += heatpump * power_to_GWh;

            }

            app.standard_demand_TWh = (input_demand_data_GWh * 0.001).toFixed(1);
        },
        draw_power_view: function () {

            app.view = "power";

            var plot_series = [
                {
                    data: timeseries(demand_plus_store_charge_data),
                    label: "Store 2 charge",
                    color: "#000",
                    lines: { show: true, fill: 0.8, lineWidth: 0 },
                    stack: false
                }, {
                    data: timeseries(demand_data),
                    label: "Demand", // orange red
                    color: "#ff4500",
                    lines: { show: true, fill: 1.0, lineWidth: 0 },
                    stack: false
                }, {
                    data: timeseries(trad_demand_data),
                    label: "Trad demand",
                    color: "#0699fa",
                    lines: { show: true, fill: 1.0, lineWidth: 0 },
                    stack: false
                }, {
                    data: timeseries(nuclear_data),
                    label: "Nuclear",
                    color: "#ff69b4",
                    lines: { show: true, fill: 0.8, lineWidth: 0 },
                    stack: true
                }, {
                    data: timeseries(wind_data),
                    label: "Wind",
                    color: "green",
                    lines: { show: true, fill: 0.8, lineWidth: 0 },
                    stack: true
                }, {
                    data: timeseries(solar_data),
                    label: "Solar",
                    color: "#dccc1f",
                    lines: { show: true, fill: 0.8, lineWidth: 0 },
                    stack: true
                }, {
                    data: timeseries(store2_discharge_data),
                    label: "Store 2 discharge",
                    // orange
                    color: "#ff8c00",
                    lines: { show: true, fill: 0.8, lineWidth: 0 },
                    stack: true
                }
            ];

            if (app.store1.capacity > 0) {
                plot_series.push({
                    data: timeseries(store1_soc_data),
                    label: "SOC",
                    color: "#000",
                    yaxis: 2,
                    lines: { show: true, fill: false, lineWidth: 1 }
                });
            }

            if (app.store2.capacity > 0) {
                plot_series.push({
                    data: timeseries(store2_soc_data),
                    label: "Store 2",
                    color: "#000",
                    yaxis: 2,
                    lines: { show: true, fill: false, lineWidth: 1 }
                });
            }

            var options = {
                xaxis: {
                    mode: "time",
                },
                yaxis: {
                    min: 0
                },
                selection: {
                    mode: "x"
                },
                grid: {
                    hoverable: true,
                    clickable: true
                }
            };

            $.plot("#graph", plot_series, options);

        },
        zoom_out: function () {
            view.zoomout();
            view.calc_interval(2400, 900);
            app.draw_power_view();
        },
        zoom_in: function () {
            view.zoomin();
            view.calc_interval(2400, 900);
            app.draw_power_view();
        },
        pan_left: function () {
            view.panleft();
            app.draw_power_view();
        },
        pan_right: function () {
            view.panright();
            app.draw_power_view();
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
    },
    mounted: function () {
        // feeds: demand, wind, solar, heatpump
        feed.getdata("477241,480172,480862,476422", "2023-04-01T00:00:00Z", "2024-04-01T00:00:00Z", this.interval, 1, function (result) {
            series = result;

            view.start = series[0].data[0][0];
            view.end = series[0].data[series[0].data.length - 1][0];
            view.calc_interval(2400, 900);

            app.normalise();
            app.update();
        });
    }
});

// return subset of data for power view - keeps things snappy
function timeseries(data) {
    if (data == undefined) return [];
    var start_time = data[0][0];
    var len = data.length;
    var ts = [];

    for (var time = view.start; time < view.end; time += view.interval * 1000) {
        let pos = Math.floor((time - start_time) / (app.interval * 1000));
        if (pos >= 0 && pos < len) {
            ts.push(data[pos]);
        }
    }
    return ts;
}

$("#graph").bind("plotselected", function (event, ranges) {
    view.start = ranges.xaxis.from;
    view.end = ranges.xaxis.to;
    view.calc_interval(2400, 900);
    app.draw_power_view();
});
