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
            <h3>Volume simulator</h3>
            <p>Explore system volume, starts per hour and cycling</p>
        </div>
    </div>
    <div class="row">
        <h5>Simulation window: {{ hours | toFixed(1) }} hour<span v-if="hours>1.0">s</span></h5>
        <div id="graph_bound" style="width:100%; height:400px; position:relative; ">
            <div id="graph"></div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Minimum heat output</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="minimum_heat_output"
                                    @change="simulate" />
                                <span class="input-group-text">W</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Heat demand</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="heat_demand" @change="simulate"
                                    @change="simulate" />
                                <span class="input-group-text">W</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Heat emitter rated output @ DT50</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control"
                                    v-model.number="radiatorRatedOutput" @change="simulate" />
                                <span class="input-group-text">W</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">System DT (Fixed)</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control"
                                    v-model.number="system_DT" @change="simulate" />
                                <span class="input-group-text">°K</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Minimum compressor run time</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="minimum_on_time_min" @change="simulate" />
                                <span class="input-group-text">min</span>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Maximum starts per hour</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="max_starts_per_hour" @change="simulate" />
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Cycles to simulate</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="cycles_to_simulate" @change="simulate" />
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">System volume</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="system_volume" @change="simulate" />
                                <span class="input-group-text">L</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Compressor run time</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="on_time" disabled
                                    @change="simulate" />
                                <span class="input-group-text">min</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Starts per hour</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" v-model.number="starts_per_hour" disabled
                                    @change="simulate" />
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">MWT DT</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control"
                                    v-model.number="mwt_DT" disabled />
                                <span class="input-group-text">°K</span>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Return DT</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control"
                                    v-model.number="return_DT" disabled />
                                <span class="input-group-text">°K</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <br>


            <div class="card">
                <div class="card-body">
                    <p>Simplified minimum volume calculation:</p>
                    <p><i>Minimum volume = (minimum heat output × minimum compressor on time) ÷ (specific heat × target DT)</i></p>
                    <p>Above example: ({{ minimum_heat_output }}W × {{ minimum_on_time_min*60 }}s) ÷ (4187 J/kg.K × 5K) = <b>{{ suggested_volume | toFixed(0) }} Litres</b></p>
                    <p><i>Target DT range is typically 5-10K</i></p>
                    <p>Think of this as the volume required to provide the compressor with the minimum on time under a zero load (no emitter output) condition assuming the volume increases in temperature by the target DT given.</p>

                </div>
            </div>


        </div>
    </div>
</div>
<script src="<?php echo $path; ?>volume_sim.js?v=16"></script>