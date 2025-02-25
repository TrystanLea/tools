
var app = new Vue({
    el: '#app',
    data: {
        HP_flowrate_lmin: 12,
        HE_flowrate_lmin: 12,
        HP_flowrate: 0.2,
        HE_flowrate: 0.2,
        heat_capacity: 4187,
        HP_flowT: 20,
        HP_returnT: 20,
        HE_returnT: 20,
        HE_flowT: 20,
        room: 20,
        outside: -3,
        heatpump_output: 4000,
        rated_output: 15000,
        rated_dT: 50,
        radiator_output: 0,
        heatpump_COP: 0,
        radiator_output_compare: 0,
        heatpump_COP_compare: 0,
        HP_returnT_direct: 20,
        cop_method: "carnot"
    },
    methods: {
        update: function () {
            app.HP_flowrate = app.HP_flowrate_lmin / 60;
            app.HE_flowrate = app.HE_flowrate_lmin / 60;

            app.radiator_output = app.heatpump_output;

            let MWT_minus_room = Math.pow(app.radiator_output / app.rated_output, 1 / 1.3) * app.rated_dT;
            let MWT = MWT_minus_room + app.room

            let radiator_dT = app.radiator_output / (app.heat_capacity * app.HE_flowrate)

            app.HE_flowT = MWT + (radiator_dT * 0.5)
            app.HE_returnT = MWT - (radiator_dT * 0.5)

            let heatpump_dT = (app.HE_flowrate * radiator_dT) / app.HP_flowrate

            if (app.HE_flowrate > app.HP_flowrate) {
                app.HP_returnT = app.HE_returnT
                app.HP_flowT = app.HP_returnT + heatpump_dT
            } else {
                app.HP_flowT = app.HE_flowT
                app.HP_returnT = app.HP_flowT - heatpump_dT
            }

            if (app.cop_method == "ecodan") {
                app.heatpump_COP = get_ecodan_cop(app.HP_flowT, app.outside, app.radiator_output / 5000)
                app.heatpump_COP_compare = get_ecodan_cop(app.HE_flowT, app.outside, app.radiator_output / 5000)
            } else {
                app.heatpump_COP = heatpump_COP(app.HP_flowT, app.outside)
                app.heatpump_COP_compare = heatpump_COP(app.HE_flowT, app.outside)
            }

            // Search for heat output from radiators at given flow temperature and flow rate
            app.HP_returnT_direct = app.HP_flowT
            MWT = app.HP_flowT
            app.radiator_output_compare = 0
            for (var i = 0; i < 100; i++) {
                MWT = (app.HP_flowT + app.HP_returnT_direct) * 0.5
                let last_heat_output = app.radiator_output_compare
                app.radiator_output_compare = app.rated_output * Math.pow(((MWT - app.room) / app.rated_dT), 1.3)
                dT = app.radiator_output_compare / (app.heat_capacity * app.HP_flowrate)
                app.HP_returnT_direct = app.HP_flowT - dT
                if (Math.round(app.radiator_output_compare * 100) == Math.round(last_heat_output * 100)) {
                    break;
                }
            }
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

app.update();

function heatpump_COP(flowT, outsideT) {
    T_condensing = flowT + 4
    T_refrigerant = outsideT - 6
    Carnot_COP = (T_condensing + 273) / ((T_condensing + 273) - (T_refrigerant + 273))
    Practical_COP = 0.5 * Carnot_COP
    return Practical_COP
}