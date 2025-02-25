
<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>

<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.time.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/fontawesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/solid.min.css">

<div class="container" style="max-width:1200px" id="app">
    <div class="row">
        <div class="col">
            <br>
            <h3>Dynamic heat pump simulator</h3>
            <p>Explore continuous vs intermittent heating, temperature set-backs and schedules.</p>
            <div class="alert alert-warning"><i class="fa-solid fa-person-digging"></i> This tool is work in
                progress. It does not implement defrosts and general implementation is a crude simplification. Help welcome to make this tool better.</div>
        </div>
    </div>
    <div class="row">
        <div id="graph_bound" style="width:100%; height:400px; position:relative; ">
            <div id="graph"></div>
        </div>
    </div>
    <br><br>
    <div class="row">
        <div class="col">
            <label class="form-label">Electric input</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="results.elec_kwh | toFixed(3)" disabled>
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col">    
            <label class="form-label">Saving vs steady state</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="results.elec_saving_prc | toFixed(1)" disabled>
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Heat output</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="results.heat_kwh | toFixed(3)" disabled>
                <span class="input-group-text">kWh</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">Saving vs steady state</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" :value="results.heat_saving_prc | toFixed(1)" disabled>            
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col">
            <label class="form-label">COP</label>
            <span class="input-group mb-3">
                <input type="text" class="form-control" :value="(results.heat_kwh/results.elec_kwh) | toFixed(2)"
                    disabled>
                <button type="button" class="btn btn-warning" @click="simulate">Refine</button>

            </span>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h4>Schedule</h4>
                    <table class="table">
                        <tr>
                            <th>Time</th>
                            <th>Set point</th>
                            <th>Max FlowT</th>
                            <th><button class="btn" @click="add_space"><i class="fas fa-plus"></i></button></th>
                        </tr>
                        <tr v-for="(item,index) in schedule">
                            <td><input type="text" class="form-control" v-model="item.start" @change="simulate"
                                    style="width:75px" /></td>
                            <td>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" v-model.number="item.set_point"
                                        @change="simulate" style="width:30px" />
                                    <span class="input-group-text">°C</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" v-model.number="item.flowT"
                                        @change="simulate" style="width:30px" />
                                    <span class="input-group-text">°C</span>
                                </div>
                            </td>
                            <td><button class="btn" @click="delete_space(index)"><i
                                        class="fas fa-trash"></i></button></td>
                        </tr>
                    </table>
                </div>
            </div>
            <br>
            <div class="card">
                <div class="card-body">
                    <h4>Building fabric ({{ building.fabric_WK | toFixed(0) }} W/K)</h4>
                    <table class="table">
                        <tr v-for="(layer,index) in building.fabric">
                            <td>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" v-model.number="layer.WK" @change="simulate" />
                                    <span class="input-group-text">W/K</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" v-model.number="layer.kWhK" @change="simulate" />
                                    <span class="input-group-text">kWh/K</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" v-model.number="layer.T"  @change="simulate" />
                                    <span class="input-group-text">°C</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">

                    <label class="form-label">Control mode:</label>
                    
                    <select class="form-control" v-model="control.mode" @change="simulate">
                        <option value=0>Auto adapt 3 term PID controller</option>
                        <option value=1>Weather compensation with parallel shift</option>
                    </select>

                </div>
            </div>
            <br>          
            <div class="card" v-if="control.mode==0">
                <div class="card-body">

                    <label class="form-label">Auto adapt 3 term PID controller:</label>

                    <div class="row">
                        <div class="col">
                            <label class="form-label">Proportional</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">Kp</span>
                                <input type="text" class="form-control" v-model.number="control.Kp"
                                    @change="simulate" />
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label">Integral</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">Ki</span>
                                <input type="text" class="form-control" v-model.number="control.Ki"
                                    @change="simulate" />
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label">Derivative</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">Kd</span>
                                <input type="text" class="form-control" v-model.number="control.Kd"
                                    @change="simulate" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="card" v-if="control.mode==1">
                <div class="card-body">
                
                    <label class="form-label">Weather compensation outside temperature response:</label>
                    
                    <select class="form-control" v-model.number="control.wc_use_outside_mean" @change="simulate">
                        <option value=0>Instantaneous</option>
                        <option value=1>Average temperature for the day</option>
                    </select>
                    <br>
                    <p><i>Curve automatically selected based on building heat loss, internal gains and heat emitter spec.</i></p>
                </div>
            </div>       
            <br>

            <div class="card">
                <div class="card-body">

                    <div class="row">
                        <div class="col">
                            <label class="form-label">Heat pump capacity</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="heatpump.capacity"
                                    @change="simulate" />
                                <span class="input-group-text">W</span>
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label">System DT</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="heatpump.system_DT"
                                    @change="simulate" />
                                <span class="input-group-text">K</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label class="form-label">Heat emitter rated output</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control"
                                    v-model.number="heatpump.radiatorRatedOutput" @change="simulate"
                                    />
                                <span class="input-group-text">W</span>
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label">@ rated DT</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="heatpump.radiatorRatedDT"
                                    @change="simulate" />
                                <span class="input-group-text">K</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label class="form-label">Sytem volume</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control"
                                    v-model.number="heatpump.system_water_volume" @change="simulate"
                                    />
                                <span class="input-group-text">L</span>
                            </div>
                        </div>
                        <div class="col">
                        </div>
                    </div>

                </div>
            </div>
            <br>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <label class="form-label">Outside temperature</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="external.mid"
                                    @change="simulate" />
                                <span class="input-group-text">°C</span>
                            </div>
                        </div>
                        <div class="col">
                            <label class="form-label">Outside temperature swing</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="external.swing"
                                    @change="simulate" />
                                <span class="input-group-text">°C</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label class="form-label">Minimum</label>
                            <input type="text" class="form-control" v-model="external.min_time" @change="simulate" />
                        </div>
                        <div class="col">
                            <label class="form-label">Maximum</label>
                            <input type="text" class="form-control" v-model="external.max_time" @change="simulate" />
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="card">
                <div class="card-body">
                    <p><b>Internal gains:</b></p>
                    <p>Body heat (approx 60W per person), Electric consumption for lights, appliances and cooking ~210W (5 kWh/d), solar gains could be added here too.</p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" v-model.number="building.internal_gains" @change="simulate" />
                        <span class="input-group-text">W</span>
                    </div>
                </div>
            </div>
            <!--
            <label class="form-label">Minimum modulation</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control" v-model.number="heatpump.min_modulation" @change="simulate"/>
                <span class="input-group-text">W</span>
            </div>
            -->

        </div>
    </div>
</div>
<script src="<?php echo $path; ?>dynamic_heatpump_v1.js?v=7"></script>
