
<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.time.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.selection.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.stack.min.js"></script>

<script src="<?php echo $path_lib; ?>feed.js?v=1"></script>
<script src="<?php echo $path_lib; ?>vis.helper.js?v=1"></script>


<div class="container" style="max-width:800px" id="app">
    <div class="row">
        <div class="col">
            <br>
            <h3>Storage simulator</h3>
            <p>Explore how much home electric + heat pump demand can be met by different mixes of wind, solar, nuclear, battery storage, long duration energy storage or other final backup supply.</p>
        </div>
    </div>
    <hr>

    <div v-if="view=='power'">

        <!-- button group nav + - < > -->
        <div class="btn-group" style="float:right">
            <button class="btn btn-secondary btn-sm" @click="zoom_in">+</button>
            <button class="btn btn-secondary btn-sm" @click="zoom_out">-</button>

            <button class="btn btn-secondary btn-sm" @click="pan_left"><</button>
            <button class="btn btn-secondary btn-sm" @click="pan_right">></button>
        </div>
    
        <p>Power view (kW):</p>
    </div>
    <div class="row">
        <div class="col">
            <!-- A simple flot graph -->
            <div id="graph" style="width:100%;height:350px;"></div>
        </div>
    </div>

    <hr>
    <p style="text-align:center">Demand supplied directly before storage: <span><b>{{ balance.before_store1*100 | toFixed(0)}} %</b></span></p>
    <hr>


    <p><b>Battery storage:</b></p>
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Capacity</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="store1.capacity" @change="update">
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Round trip efficiency</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="store1.round_trip_efficiency" @change="update">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Max charge/discharge</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="store1.charge_max" @change="update">
                <span class="input-group-text">kW</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Cycles/year</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="store1.cycles | toFixed(0)" disabled>
            </div>
        </div>
    </div> 

    <p style="text-align:center">Balance after battery storage: <span><b>{{ balance.after_store1*100 | toFixed(0)}} %</b></span></p>

    <hr>

    <p><b>Long duration energy store (e.g: Hydrogen, e-Methanol):</b></p>
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <label class="form-label">Capacity</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="store2.capacity" @change="update">
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12">
            <label class="form-label">Charge efficiency</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="store2.charge_efficiency" @change="update">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12">
            <label class="form-label">Discharge efficiency</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="store2.discharge_efficiency" @change="update">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-12">
            <label class="form-label">Max charge rate</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="store2.charge_max" @change="update">
                <span class="input-group-text">kW</span>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12">
            <label class="form-label">Max discharge rate</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="store2.discharge_max" @change="update">
                <span class="input-group-text">kW</span>
            </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Cycles/year</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="store2.cycles | toFixed(1)" disabled>
            </div>
        </div>

    </div>

    <p style="text-align:center">Balance after LDES: <span><b>{{ balance.after_store2*100 | toFixed(0)}} %</b></span></p>

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


    <p><b>Solar generation:</b></p>

    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Percentage of demand</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="solar_prc_of_demand" @change="update">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Annual generation</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="solar_kwh | toFixed(0)" disabled>
                <span class="input-group-text">kWh</span>
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
            <label class="form-label">Solar kWp</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="solar_kWp | toFixed(1)" disabled>
                <span class="input-group-text">kW</span>
            </div>
        </div>
    </div>
    <hr>

    <p><b>Wind generation:</b></p>

    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Percentage of demand</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="wind_prc_of_demand" @change="update">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Annual generation</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="wind_kwh | toFixed(0)" disabled>
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Capacity factor</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="wind_cap_factor" @change="update">
                <span class="input-group-text">%</span>
            </div>
        </div>            
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Wind kWp</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="wind_kWp | toFixed(1)" disabled>
                <span class="input-group-text">kWp</span>
            </div>
        </div>
    </div>
    <hr>

    <p><b>Nuclear generation:</b></p>

    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Percentage of demand</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="nuclear_prc_of_demand" @change="update">
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Annual generation</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="nuclear_kwh | toFixed(0)" disabled>
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Capacity factor</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="nuclear_cap_factor" @change="update">
                <span class="input-group-text">%</span>
            </div>
        </div>            
        <div class="col-lg-3 col-md-3 col-sm-12">
            <label class="form-label">Nuclear kWp</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="nuclear_kWp | toFixed(1)" disabled>
                <span class="input-group-text">kWp</span>
            </div>
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
var input_wind_data_kwh = 0;

// Power series
var solar_data = [];
var wind_data = [];
var nuclear_data = [];
var demand_data = [];
var store1_soc_data = [];
var store2_soc_data = [];
var store2_discharge_data = [];

var month_timestamps = [];

var app = new Vue({
    el: '#app',
    data: {

        // solar generation
        solar_prc_of_demand: 30,
        solar_kWh_per_kWp: 870,
        solar_kWp: 0,
        solar_kwh: 0,

        // wind generation
        wind_prc_of_demand: 100,
        wind_cap_factor: 40,
        wind_kWp: 0,
        wind_kwh: 0,

        // nuclear generation
        nuclear_prc_of_demand: 0,
        nuclear_cap_factor: 90,
        nuclear_kWp: 0,
        nuclear_kwh: 0,

        // demand
        lac_demand: 1800,
        heat_demand: 9000,
        heatpump_scop: 4.0,
        heatpump_elec_kwh: 0,
        total_elec_kwh: 0,

        demand_met: "---",

        balance: {
            before_store1: 0,
            after_store1: 0,
            after_store2: 0,
            surplus: 0,
            unmet: 0
        },

        store1: {
            capacity: 10,
            soc_start: 0,
            charge_kwh: 0,
            discharge_kwh: 0,
            charge_max: 3.5,
            discharge_max: 3.5,
            round_trip_efficiency: 80
        },

        store2: {
            charge_max:1.0,
            charge_efficiency: 80,
            discharge_max: 1.6,
            discharge_efficiency: 50,
            capacity: 700,
            starting_soc: 100,
            charge_kwh: 0,
            discharge_kwh: 0,
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
        model: function() {
            app.run_count++;

            // Demand
            app.heatpump_elec_kwh = app.heat_demand / app.heatpump_scop;
            app.total_elec_kwh = app.lac_demand + app.heatpump_elec_kwh;

            // Solar generation
            app.solar_kwh = (app.solar_prc_of_demand/100) * app.total_elec_kwh;
            app.solar_kWp = app.solar_kwh / app.solar_kWh_per_kWp;

            // Wind generation
            app.wind_kwh = (app.wind_prc_of_demand/100) * app.total_elec_kwh;
            let wind_average_power = app.wind_kwh / (365*24);
            app.wind_kWp = wind_average_power / (app.wind_cap_factor / 100);

            // Nuclear generation
            app.nuclear_kwh = (app.nuclear_prc_of_demand/100) * app.total_elec_kwh;
            let nuclear_average_power = app.nuclear_kwh / (365*24);
            app.nuclear_kWp = nuclear_average_power / (app.nuclear_cap_factor / 100);

            // reset power series
            solar_data = [];
            wind_data = [];
            nuclear_data = [];
            demand_data = [];
            store1_soc_data = [];
            store2_soc_data = [];
            store2_discharge_data = [];

            let solar_kwh = 0;
            let wind_kwh = 0;
            let nuclear_kwh = 0;
            let supply_kwh = 0;
            let demand_kwh = 0;

            let deficit_before_store1_kwh = 0;
            let deficit_after_store1_kwh = 0;
            let deficit_after_store2_kwh = 0;

            let balance_surplus = 0;
            let balance_unmet = 0;

            let peak_shaving_balance = 0;

            let store1_charge_kwh = 0;
            let store1_discharge_kwh = 0;
            let store1_max_charge = 0;
            let store1_max_discharge = 0;

            let store2_charge_kwh = 0;
            let store2_discharge_kwh = 0;
            let store2_max_charge = 0;
            let store2_max_discharge = 0;
            let store2_max_level = 0;
            let store2_min_level = 100000;

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
            let store1_charge_efficiency = 1 - ((1 - app.store1.round_trip_efficiency*0.01) / 2);
            let store1_discharge_efficiency = 1 - ((1 - app.store1.round_trip_efficiency*0.01) / 2);
            app.store1.discharge_max = app.store1.charge_max;

            // Setup store2
            let store2_soc = app.store2.starting_soc;
            let store2_charge_efficiency = app.store2.charge_efficiency * 0.01;
            let store2_discharge_efficiency = app.store2.discharge_efficiency * 0.01;
            
            let power_to_kwh = app.interval / 3600;

            // Normalisation factors
            let solar_normalisation_factor = app.solar_kWh_per_kWp / input_solar_data_kwh;
            let wind_normalisation_factor = app.wind_kwh / input_wind_data_kwh;
            let lac_normalisation_factor = app.lac_demand / input_lac_data_kwh;
            let heatpump_normalisation_factor = app.heatpump_elec_kwh / input_heatpump_data_kwh;

            for (var i = 0; i < series[0].data.length; i++) {
                
                // Solar generation
                let solarpv = series[0].data[i][1] * solar_normalisation_factor * app.solar_kWp * 0.001;
                let wind = series[3].data[i][1] * wind_normalisation_factor * 0.001;
                let nuclear = app.nuclear_kWp * app.nuclear_cap_factor / 100;

                // Demand
                let lac = series[1].data[i][1] * lac_normalisation_factor * 0.001;
                let heatpump = series[2].data[i][1] * heatpump_normalisation_factor * 0.001;

                let demand = lac + heatpump;
                if (demand<0) {
                    demand = 0;
                }

                var supply = solarpv + wind + nuclear;

                solar_kwh += solarpv * power_to_kwh;
                wind_kwh += wind * power_to_kwh;
                nuclear_kwh += nuclear * power_to_kwh;

                supply_kwh += supply * power_to_kwh;
                demand_kwh += demand * power_to_kwh;

                // Balance
                var balance = supply - demand;

                // Record deficit before store1 storage
                if (balance<0) {
                    let deficit_before_store1 = -balance;
                    deficit_before_store1_kwh += deficit_before_store1 * power_to_kwh;
                }

                // store1
                if (app.store1.capacity>0) {
                    if (balance>0) {
                        
                        // Charge store1
                        let charge = balance;
                        if (charge > app.store1.charge_max) {
                            charge = app.store1.charge_max;
                        }
                        let charge_after_loss = charge * store1_charge_efficiency;
                        let soc_inc = charge_after_loss * power_to_kwh;
                        // Limit charge to store1 capacity
                        if (store1_soc + soc_inc > app.store1.capacity) {
                            soc_inc = app.store1.capacity - store1_soc;
                            charge_after_loss = soc_inc * (1/power_to_kwh);
                            charge = charge_after_loss / store1_charge_efficiency;
                        }
                        if (charge>store1_max_charge) {
                            store1_max_charge = charge;
                        }
                        store1_soc += soc_inc;
                        balance -= charge;
                        store1_charge_kwh += charge * power_to_kwh;
                    } else {
                        // Discharge store1
                        let discharge = -balance;
                        if (discharge > app.store1.discharge_max*1000) {
                            discharge = app.store1.discharge_max*1000;
                        }
                        let discharge_before_loss = discharge / store1_discharge_efficiency;
                        let soc_dec = discharge_before_loss * power_to_kwh;
                        // Limit discharge to store1 SOC
                        if (store1_soc - soc_dec < 0) {
                            soc_dec = store1_soc;
                            discharge_before_loss = soc_dec * (1/power_to_kwh);
                            discharge = discharge_before_loss * store1_discharge_efficiency;
                        }
                        if (discharge>store1_max_discharge) {
                            store1_max_discharge = discharge;
                        }
                        store1_soc -= soc_dec;
                        balance += discharge;
                        store1_discharge_kwh += discharge * power_to_kwh;
                    }
                }

                // Record deficit after store1 storage
                if (balance<0) {
                    let deficit_after_store1 = -balance;
                    deficit_after_store1_kwh += deficit_after_store1 * power_to_kwh;
                }

                let store2_discharge = 0;

                // Store 2 (hydrogen, e-methanol LDES)
                if (balance>0) {
                    // Charge store
                    let charge = balance;
                    if (charge > app.store2.charge_max) {
                        charge = app.store2.charge_max;
                    }
                    let charge_after_loss = charge * store2_charge_efficiency;
                    let soc_inc = charge_after_loss * power_to_kwh;
                    // Limit charge to store capacity
                    if (store2_soc + soc_inc > app.store2.capacity) {
                        soc_inc = app.store2.capacity - store2_soc;
                        charge_after_loss = soc_inc * (1/power_to_kwh);
                        charge = charge_after_loss / store2_charge_efficiency;
                    }
                    if (charge>store2_max_charge) {
                        store2_max_charge = charge;
                    }  
                    store2_soc += soc_inc;
                    balance -= charge;
                    store2_charge_kwh += charge * power_to_kwh;
                } else {
                    // Discharge store
                    let discharge = -balance;
                    if (discharge > app.store2.discharge_max) {
                        discharge = app.store2.discharge_max;
                    }
                    // peak_shaving_balance -= (-balance - app.store2.discharge_max) * power_to_kwh;

                    let discharge_before_loss = discharge / store2_discharge_efficiency;
                    let soc_dec = discharge_before_loss * power_to_kwh;
                    // Limit discharge to store SOC
                    if (store2_soc - soc_dec < 0) {
                        soc_dec = store2_soc;
                        discharge_before_loss = soc_dec * (1/power_to_kwh);
                        discharge = discharge_before_loss * store2_discharge_efficiency;
                    }
                    if (discharge>store2_max_discharge) {
                        store2_max_discharge = discharge;
                    }   
                    store2_soc -= soc_dec;
                    balance += discharge;
                    store2_discharge_kwh += discharge * power_to_kwh;
                    store2_discharge = discharge;
                }

                // Record max and min store level
                if (store2_soc>store2_max_level) {
                    store2_max_level = store2_soc;
                }
                if (store2_soc<store2_min_level) {
                    store2_min_level = store2_soc;
                }  

                if (peak_shaving_balance>0) {
                    peak_shaving_balance = 0;
                }

                if (-peak_shaving_balance>max_peak_shaving_deficit) {
                    max_peak_shaving_deficit = -peak_shaving_balance;
                }

                // Record deficit after store2 storage
                if (balance<0) {
                    let deficit_after_store2 = -balance;
                    deficit_after_store2_kwh += deficit_after_store2 * power_to_kwh;
                } else {
                    balance_surplus += balance * power_to_kwh;
                }
                
                let time = series[0].data[i][0];
                solar_data.push([time, solarpv]);
                wind_data.push([time, wind]);
                nuclear_data.push([time, nuclear]);
                demand_data.push([time, demand]);
                store1_soc_data.push([time, store1_soc]);
                store2_soc_data.push([time, store2_soc]);
                store2_discharge_data.push([time, store2_discharge]);
                
            }

            if (app.auto_optimise) {
                if (store1_max_charge<app.store1.charge_max) {
                    app.store1.charge_max = 1*(store1_max_charge).toFixed(2);
                }
                if (store2_max_charge<app.store2.charge_max) {
                    app.store2.charge_max = 1*(store2_max_charge).toFixed(2);
                }
                if (store1_max_discharge<app.store1.discharge_max) {
                    app.store1.discharge_max = 1*(store1_max_discharge).toFixed(2);
                }
                if (store2_max_discharge<app.store2.discharge_max) {
                    app.store2.discharge_max = 1*(store2_max_discharge).toFixed(2);
                }
                // let store_diff = store2_max_level - store2_min_level;
                // app.store2.capacity = 1*(store_diff*1.1).toFixed(0);
                // app.store2.starting_soc = 1*(store_diff*0.05).toFixed(0);
            }

            app.store1.charge_CF = store1_charge_kwh / (app.store1.charge_max * 24 * 365);
            app.store2.charge_CF = store2_charge_kwh / (app.store2.charge_max * 24 * 365);  
            app.store1.discharge_CF = store1_discharge_kwh / (app.store1.discharge_max * 24 * 365);
            app.store2.discharge_CF = store2_discharge_kwh / (app.store2.discharge_max * 24 * 365);  
            
            app.balance.before_store1 = (demand_kwh - deficit_before_store1_kwh) / demand_kwh;
            app.balance.after_store1 = (demand_kwh - deficit_after_store1_kwh) / demand_kwh;
            app.balance.after_store2 = (demand_kwh - deficit_after_store2_kwh) / demand_kwh;
            app.balance.unmet = deficit_after_store2_kwh;

            app.store1.cycles = 0.5*(store1_charge_kwh + store1_discharge_kwh) / app.store1.capacity;
            app.store2.cycles = 0.5*(store2_charge_kwh + store2_discharge_kwh) / app.store2.capacity;

            // Copy over to vue (faster than using vue reactive data during model run)
            app.solar_kwh = solar_kwh;
            app.wind_kwh = wind_kwh;
            app.nuclear_kwh = nuclear_kwh;
            app.supply_kwh = supply_kwh;
            app.demand_kwh = demand_kwh;

            
            console.log("Run count: " + app.run_count);
            console.log("Annual wind: " + wind_kwh.toFixed(0) + " kWh");
            console.log("Annual solar: " + solar_kwh.toFixed(0) + " kWh");
            console.log("Annual nuclear: " + nuclear_kwh.toFixed(0) + " kWh");

            app.store1.soc_start = store1_soc;
            if (store1_soc>10 && app.run_count<3) {
                console.log("Re-running model with store1 SOC start: " + app.store1.soc_start.toFixed(2) + " kWh");
                app.model();
            }

            app.store2.starting_soc = store2_soc;
            if (store2_soc>10 && app.run_count<3) {
                console.log("Re-running model with store2 SOC start: " + app.store2.starting_soc.toFixed(2) + " kWh");
                app.model();
            }

            app.draw_power_view();
        },
        normalise: function () {
            // Normalise solar data to match solar_kWp
            input_solar_data_kwh = 0;
            input_wind_data_kwh = 0;
            input_lac_data_kwh = 0;
            input_heatpump_data_kwh = 0;

            let power_to_kwh = app.interval / 3600000;

            for (var i = 0; i < series[0].data.length; i++) {
                let solarpv = series[0].data[i][1];
                let lac = series[1].data[i][1];
                let heatpump = series[2].data[i][1];
                let wind = series[3].data[i][1];

                input_solar_data_kwh += solarpv * power_to_kwh;
                input_wind_data_kwh += wind * power_to_kwh;
                input_lac_data_kwh += lac * power_to_kwh;
                input_heatpump_data_kwh += heatpump * power_to_kwh;
            }
        },
        draw_power_view: function () {

            app.view = "power";
            
            var plot_series = [
                {
                    data: timeseries(demand_data),
                    label: "Demand",
                    color: "#0699fa",
                    lines: { show: true, fill: 0.8, lineWidth: 0},
                    stack: false
                },{
                    data: timeseries(nuclear_data),
                    label: "Nuclear",
                    color: "#ff69b4",
                    lines: { show: true, fill: 0.8, lineWidth: 0},
                    stack: true
                },{
                    data: timeseries(wind_data),
                    label: "Wind",
                    color: "green",
                    lines: { show: true, fill: 0.8, lineWidth: 0},
                    stack: true
                },{
                    data: timeseries(solar_data),
                    label: "Solar",
                    color: "#dccc1f",
                    lines: { show: true, fill: 0.8, lineWidth: 0},
                    stack: true

                }
            ];

            if (app.store1.capacity>0) {
                plot_series.push({
                    data: timeseries(store1_soc_data),
                    label: "SOC",
                    color: "#000",
                    yaxis: 2,
                    lines: { show: true, fill: false, lineWidth: 1}
                });
            }

            if (app.store2.capacity>0) {
                plot_series.push({
                    data: timeseries(store2_soc_data),
                    label: "Store 2",
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
        // feeds: solar, lac, heatpump, wind
        feed.getdata("516488,516721,458771,480172","2023-01-01T00:00:00Z","2024-01-01T00:00:00Z",this.interval,1,function(result) {
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
    view.start = ranges.xaxis.from;
    view.end = ranges.xaxis.to;
    view.calc_interval(2400, 900);
    app.draw_power_view();
});


</script>
