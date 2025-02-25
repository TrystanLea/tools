
<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.time.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.selection.min.js"></script>

<script src="<?php echo $path_lib; ?>feed.js?v=1"></script>
<script src="<?php echo $path_lib; ?>vis.helper.js?v=1"></script>

<div class="container" style="max-width:800px" id="app">
    <div class="row">
        <div class="col">
            <br>
            <h3>Explore Solar Matching</h3>
            <p>Explore how much home electric + heat pump demand can be met by solar and a battery.</p>
        </div>
    </div>
    <hr>


    <!-- right aligned button to switch to power view -->
    <div v-if="view=='monthly'">
        <button class="btn btn-warning btn-sm" style="float:right" @click="switch_to_power_view">Detailed power view</button>
        <p>Monthly demand & generation (kWh):</p>
    </div>
    <div v-if="view=='power'">

        <!-- button group nav + - < > -->
        <div class="btn-group" style="float:right">
            <button class="btn btn-secondary btn-sm" @click="zoom_in">+</button>
            <button class="btn btn-secondary btn-sm" @click="zoom_out">-</button>

            <button class="btn btn-secondary btn-sm" @click="pan_left"><</button>
            <button class="btn btn-secondary btn-sm" @click="pan_right">></button>
            <button class="btn btn-warning btn-sm" style="float:right" @click="draw_monthly_view">Monthly view</button>
        </div>
    
        <p>Power view (W):</p>
    </div>
    <div class="row">
        <div class="col">
            <!-- A simple flot graph -->
            <div id="graph" style="width:100%;height:350px;"></div>
        </div>
    </div>
    <p>Demand from solar: <span><b>{{ prc_from_solar | toFixed(0)}} %</b></span></p>
    <hr>

    <p><b>Solar generation & battery:</b></p>

    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Solar kWp</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="solar_kWp" @change="update">
                <span class="input-group-text">kW</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Solar kWh/kWp</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="solar_kWh_per_kWp" @change="update">
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Annual solar generation</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="annual_solar_kwh | toFixed(0)" disabled>
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Battery</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="battery.capacity" @change="update">
                <span class="input-group-text">kWh</span>
            </div>
        </div>
    </div>
    <hr>

    <p><b>Electric demand & heat pump:</b></p>

    <div class="row">
        <div class="col-md col-sm-12">
            <label class="form-label">Standard electric</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="lac_demand" @change="update">
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col-md col-sm-12">
            <label class="form-label">Heat demand</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="heat_demand" @change="update">
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col-md-2 col-sm-12">
            <label class="form-label">SCOP</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="heatpump_scop" @change="update">
            </div>
        </div>
        <div class="col-md col-sm-12">
            <label class="form-label">Heatpump electric</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="heatpump_elec_kwh | toFixed(0)" disabled>
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col-md col-sm-12">
            <label class="form-label">Total</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="total_elec_kwh | toFixed(0)" disabled>
                <span class="input-group-text">kWh</span>
            </div>
        </div>
    </div>
    <hr>

    <p><b>Electric cost and savings:</b></p>

    <div class="row">
        <div class="col-md col-sm-12">
            <label class="form-label">Import rate</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="import_rate" @change="update">
                <span class="input-group-text">p/kWh</span>
            </div>
        </div>
        <div class="col-md col-sm-12">
            <label class="form-label">Export rate</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="export_rate" @change="update">
                <span class="input-group-text">p/kWh</span>
            </div>
        </div>
        <div class="col-md col-sm-12">
            <label class="form-label">Annual cost</label>
            <div class="input-group mb-3">
                <span class="input-group-text">£</span>
                <input type="text" class="form-control" :value="annual_cost | toFixed(0)" disabled>
            </div>
        </div>
        <div class="col-md col-sm-12">
            <label class="form-label">Saving</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="saving | toFixed(0)" disabled>
                <span class="input-group-text">%</span>

            </div>
        </div>
    </div>

    <hr> 
    <div class="row">
        <div class="col">
            <table class="table">
                <tr>
                    <th>Month</th>
                    <th>Solar</th>
                    <th>Demand</th>
                    <th>Import</th>
                    <th>Export</th>
                    <th>From solar</th>
                </tr>
                <tr v-for="month in monthly_table">
                    <td>{{month.month}}</td>
                    <td>{{month.solar_kwh | toFixed(0)}} kWh</td>
                    <td>{{month.demand_kwh | toFixed(0)}} kWh</td>
                    <td>{{month.import_kwh | toFixed(0)}} kWh</td>
                    <td>{{month.export_kwh | toFixed(0)}} kWh</td>
                    <td>{{100 * (month.demand_kwh - month.import_kwh) / month.demand_kwh | toFixed(0)}} %</td>
                </tr>
                <!-- totals-->
                <tr>
                    <td><b>Total</b></td>
                    <td><b>{{annual_solar_kwh | toFixed(0)}} kWh</b></td>
                    <td><b>{{annual_demand_kwh | toFixed(0)}} kWh</b></td>
                    <td><b>{{annual_import_kwh | toFixed(0)}} kWh</b></td>
                    <td><b>{{annual_export_kwh | toFixed(0)}} kWh</b></td>
                    <td><b>{{100 * (annual_demand_kwh - annual_import_kwh) / annual_demand_kwh | toFixed(0)}} %</b></td>
                </tr>
            </table>
        </div>
    </div>
</div>


<script>
// Start by loading hourly average outside temperature data from emoncms.org API
// Create basic Vue outline
var series = [];

// Used for input data normalisation
var input_solar_data_kwh = 0;
var input_lac_data_kwh = 0;
var input_heatpump_data_kwh = 0;

// Power series
var solar_data = [];
var demand_data = [];
var soc_data = [];

var month_timestamps = [];

var app = new Vue({
    el: '#app',
    data: {
        // inputs
        solar_kWp: 4,
        solar_kWh_per_kWp: 870,

        // demand
        lac_demand: 1800,
        heat_demand: 9000,
        heatpump_scop: 4.0,
        heatpump_elec_kwh: 0,
        total_elec_kwh: 0,

        prc_from_solar: "---",

        battery: {
            capacity: 0,
            soc: 0,
            soc_start: 0,
            charge_kwh: 0,
            discharge_kwh: 0,
            charge_max: 3.5,
            discharge_max: 3.5,
            charge_efficiency: 0.9,
            discharge_efficiency: 0.9
        },

        import_rate: 23,
        export_rate: 15,
        annual_cost: 0,
        saving: 0,
        annual_cost_str: '',

        // outputs
        annual_solar_kwh: 0,
        annual_demand_kwh: 0,
        annual_import_kwh: 0,
        annual_export_kwh: 0,

        monthly_solar_kwh: [],
        monthly_demand_kwh: [],
        monthly_import_kwh: [],
        monthly_export_kwh: [],
        monthly_solar_self_use_kwh: [],

        monthly_table: [],

        interval: 900,
        run_count: 0,

        view: "monthly" // or "power"
    },
    methods: {
        update: function () {
            console.log("---- Update ----");
            app.run_count = 0;
            app.model();
        },
        model: function() {
            app.run_count++;

            app.heatpump_elec_kwh = app.heat_demand / app.heatpump_scop;
            app.total_elec_kwh = app.lac_demand + app.heatpump_elec_kwh;

            var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

            // reset for new calculation
            let monthly_solar_kwh = [];
            let monthly_demand_kwh = [];
            let monthly_export_kwh = [];
            let monthly_solar_self_use_kwh = [];
            let monthly_soc = [];
            let monthly_table = [];
            month_timestamps = [];

            // reset power series
            solar_data = [];
            demand_data = [];
            soc_data = [];

            let annual_solar_kwh = 0;
            let annual_demand_kwh = 0;
            let annual_import_kwh = 0;
            let annual_export_kwh = 0;

            let month_solar_kwh = 0;
            let month_demand_kwh = 0;
            let month_import_kwh = 0;
            let month_export_kwh = 0;

            let battery_soc = app.battery.soc_start;
            app.battery.charge_max = app.solar_kWp * 1000;
            app.battery.discharge_max = app.solar_kWp * 1000;
            
            let month = new Date(series[0].data[0][0]).getMonth();

            let power_to_kwh = app.interval / 3600000;

            let solar_normalisation_factor = app.solar_kWh_per_kWp / input_solar_data_kwh;
            let lac_normalisation_factor = app.lac_demand / input_lac_data_kwh;
            let heatpump_normalisation_factor = app.heatpump_elec_kwh / input_heatpump_data_kwh;

            let month_start = series[0].data[0][0];
            let month_end = series[0].data[0][0];

            for (var i = 0; i < series[0].data.length; i++) {
                
                // Solar generation
                let solarpv = series[0].data[i][1] * solar_normalisation_factor * app.solar_kWp;
                // Demand
                let lac = series[1].data[i][1] * lac_normalisation_factor;
                let heatpump = series[2].data[i][1] * heatpump_normalisation_factor;

                let demand = lac + heatpump;

                // Balance
                var balance = solarpv - demand;

                // Battery
                if (app.battery.capacity>0) {
                    if (balance>0) {
                        
                        // Charge battery
                        let charge = balance;
                        // if (charge > app.battery.charge_max) {
                        //     charge = app.battery.charge_max;
                        // }
                        let charge_after_loss = charge * app.battery.charge_efficiency;
                        let soc_inc = charge_after_loss * power_to_kwh;
                        // Limit charge to battery capacity
                        if (battery_soc + soc_inc > app.battery.capacity) {
                            soc_inc = app.battery.capacity - battery_soc;
                            charge_after_loss = soc_inc * (1/power_to_kwh);
                            charge = charge_after_loss / app.battery.charge_efficiency;
                        }
                        // if (charge>app.battery.max_charge) {
                        //     app.battery.max_charge = charge;
                        // }
                        battery_soc += soc_inc;
                        balance -= charge;
                        // app.battery.charge_kwh += charge * power_to_kwh;
                    } else {
                        // Discharge battery
                        let discharge = -balance;
                        // if (discharge > app.battery.discharge_max) {
                        //     discharge = app.battery.discharge_max;
                        // }
                        let discharge_before_loss = discharge / app.battery.discharge_efficiency;
                        let soc_dec = discharge_before_loss * power_to_kwh;
                        // Limit discharge to battery SOC
                        if (battery_soc - soc_dec < 0) {
                            soc_dec = battery_soc;
                            discharge_before_loss = soc_dec * (1/power_to_kwh);
                            discharge = discharge_before_loss * app.battery.discharge_efficiency;
                        }
                        // if (discharge>app.battery.max_discharge) {
                        //     app.battery.max_discharge = discharge;
                        // }   
                        battery_soc -= soc_dec;
                        balance += discharge;
                        // app.battery.discharge_kwh += discharge * power_to_kwh;
                    }
                }

                var export_to_grid = 0;
                var import_from_grid = 0;

                if (balance>0) {
                    export_to_grid = balance;
                } else {
                    import_from_grid = -balance;
                }
                
                // convert to cumulative kWh
                annual_solar_kwh += solarpv * power_to_kwh;
                annual_demand_kwh += demand * power_to_kwh;
                annual_import_kwh += import_from_grid * power_to_kwh;
                annual_export_kwh += export_to_grid * power_to_kwh;

                month_solar_kwh += solarpv * power_to_kwh;
                month_demand_kwh += demand * power_to_kwh;
                month_import_kwh += import_from_grid * power_to_kwh;
                month_export_kwh += export_to_grid * power_to_kwh;

                // Create timeseries of monthly heat demand for graph and table
                
                var time = series[0].data[i][0];
                let lastMonth = month;
                month = new Date(time).getMonth();
                if (month != lastMonth) {

                    month_end = time;
                    month_timestamps.push([month_start, month_end]);
                    month_start = time;

                    let xval =  lastMonth + 1;

                    let month_solar_self_use_kwh = month_solar_kwh - month_export_kwh;

                    monthly_solar_kwh.push([xval, month_solar_kwh]);
                    monthly_solar_self_use_kwh.push([xval, month_solar_self_use_kwh]);
                    monthly_demand_kwh.push([xval, month_demand_kwh]);
                    monthly_export_kwh.push([xval, month_export_kwh*-1]);

                    monthly_soc.push([xval, battery_soc]);
                    
                    monthly_table.push({
                        month: months[lastMonth],
                        solar_kwh: month_solar_kwh,
                        demand_kwh: month_demand_kwh,
                        import_kwh: month_import_kwh,
                        export_kwh: month_export_kwh
                    });

                    month_solar_kwh = 0;
                    month_demand_kwh = 0;
                    month_import_kwh = 0;
                    month_export_kwh = 0;
                }

                solar_data.push([time, solarpv]);
                demand_data.push([time, demand]);
                soc_data.push([time, battery_soc]);
                
            }

            app.annual_solar_kwh = annual_solar_kwh;
            app.annual_demand_kwh = annual_demand_kwh;
            app.annual_import_kwh = annual_import_kwh;
            app.annual_export_kwh = annual_export_kwh;

            app.monthly_solar_kwh = monthly_solar_kwh;
            app.monthly_demand_kwh = monthly_demand_kwh;
            app.monthly_export_kwh = monthly_export_kwh;
            app.monthly_solar_self_use_kwh = monthly_solar_self_use_kwh;
            app.monthly_soc = monthly_soc;
            app.monthly_table = monthly_table;

            app.annual_cost = (app.annual_import_kwh * app.import_rate * 0.01) - (app.annual_export_kwh * app.export_rate * 0.01);
            app.saving = 100*(1 - (app.annual_cost / (app.import_rate * app.annual_demand_kwh * 0.01)));
            app.prc_from_solar = 100 * (app.annual_demand_kwh - app.annual_import_kwh) / app.annual_demand_kwh;


            console.log("Run count: " + app.run_count);

            app.battery.soc_start = battery_soc;
            if (battery_soc>10 && app.run_count<3) {
                console.log("Re-running model with SOC start: " + app.battery.soc_start.toFixed(2) + " kWh");
                app.model();
            }

            if (app.view == "monthly") {
                app.draw_monthly_view();
            } else {
                app.draw_power_view();
            }
        },
        normalise: function () {
            // Normalise solar data to match solar_kWp
            input_solar_data_kwh = 0;
            input_lac_data_kwh = 0;
            input_heatpump_data_kwh = 0;

            let power_to_kwh = app.interval / 3600000;

            for (var i = 0; i < series[0].data.length; i++) {
                let solarpv = series[0].data[i][1];
                let lac = series[1].data[i][1];
                let heatpump = series[2].data[i][1];

                input_solar_data_kwh += solarpv * power_to_kwh;
                input_lac_data_kwh += lac * power_to_kwh;
                input_heatpump_data_kwh += heatpump * power_to_kwh;
            }
        },
        draw_monthly_view: function () {

            app.view = "monthly";

            var plot_series = [
                {
                    data: app.monthly_demand_kwh,
                    label: "Demand",
                    color: "#0699fa",
                    bars: { show: true, align: "center", fill: 0.8, lineWidth:0, barWidth: 0.8}
                },{
                    data: app.monthly_solar_self_use_kwh,
                    label: "Self use",
                    color: "#dccc1f",
                    bars: { show: true, align: "center", fill: 0.6, lineWidth:0, barWidth: 0.8}
                },
                {
                    data: app.monthly_export_kwh,
                    label: "Export",
                    color: "#dccc1f",
                    bars: { show: true, align: "center", fill: 0.8, lineWidth:0, barWidth: 0.8}
                }
            ];

            if (app.battery.capacity>100) {
                plot_series.push({
                    data: app.monthly_soc,
                    label: "SOC",
                    color: "#000",
                    yaxis: 2,
                    lines: { show: true, fill: false, lineWidth: 1}
                });
            }

            var options = {
                xaxis: {
                    // time
                    // mode: "time",
                },
                yaxis: {
                },
                selection: {
                    mode: "x"
                },
                grid:{
                    hoverable: true,
                    clickable: true
                }
            };

            $.plot("#graph", plot_series, options);

        },
        draw_power_view: function () {

            app.view = "power";
            
            var plot_series = [
                {
                    data: timeseries(demand_data),
                    label: "Demand",
                    color: "#0699fa",
                    lines: { show: true, fill: 0.8, lineWidth: 0}
                },{
                    data: timeseries(solar_data),
                    label: "Solar",
                    color: "#dccc1f",
                    lines: { show: true, fill: 0.8, lineWidth: 0}
                }
            ];

            if (app.battery.capacity>0) {
                plot_series.push({
                    data: timeseries(soc_data),
                    label: "SOC",
                    color: "#000",
                    yaxis: 2,
                    lines: { show: true, fill: false, lineWidth: 1}
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
                grid:{
                    hoverable: true,
                    clickable: true
                }
            };

            $.plot("#graph", plot_series, options);

        },
        switch_to_power_view: function () {
            app.view = "power";
            view.start = series[0].data[0][0];
            view.end = series[0].data[series[0].data.length-1][0];
            view.calc_interval(2400, 900);
            app.draw_power_view();
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
        feed.getdata("516488,516721,458771","2023-01-01T00:00:00Z","2024-01-01T00:00:00Z",this.interval,1,function(result) {
            series = result;

            

            view.start = series[0].data[0][0];
            view.end = series[0].data[series[0].data.length-1][0];
            view.calc_interval(2400, 900);

            app.normalise();
            app.update();
        });
    }
});

// return subset of data for power view - keeps things snappy
function timeseries(data)
{
    if (data==undefined) return [];
    var start_time = data[0][0];
    var len = data.length;
    var ts = [];

    for (var time=view.start; time<view.end; time+=view.interval*1000) {
        let pos = Math.floor((time-start_time)/(app.interval*1000));
        if (pos>=0 && pos<len) {
            ts.push(data[pos]);
        }
    }
    return ts;
}

$("#graph").bind("plotselected", function (event, ranges)
{
    if (app.view == "monthly") {
        return;
    }

    view.start = ranges.xaxis.from;
    view.end = ranges.xaxis.to;
    view.calc_interval(2400, 900);
    app.draw_power_view();
});

// Auto click through to power graph
$('#graph').bind("plotclick", function (event, pos, item) {
    if (item && app.view == "monthly") {
        var month_index = item.dataIndex;

        view.start = month_timestamps[month_index][0];
        view.end = month_timestamps[month_index][1];
        view.calc_interval(2400, 900);
        app.view = "power";
        app.draw_power_view();
    }
});

</script>
