
var app = new Vue({
    el: '#app',
    data: {
        htc: 1200,
        area: 1.0,
        mh_lmin: 12,
        mc_lmin: 12,
        mh: 0.2,
        mc: 0.2,
        Cph: 4020,
        Cpc: 4187,
        Thi: 20,
        Tho: 20,
        Tci: 20,
        Tco: 20,
        room: 20,
        outside: -3,
        heatpump_output: 4000,
        rated_output: 15000,
        rated_dT: 50,
        radiator_output: 0,
        heatpump_COP: 0,
        radiator_output_compare: 0,
        heatpump_COP_compare: 0,
        Tho_direct: 20,
        cop_method: "carnot"
    },
    methods: {
        update: function() {
            app.mh = app.mh_lmin / 60;
            app.mc = app.mc_lmin / 60;

            var UA = app.htc * app.area
            var Cmin = calc_Cmin(app.mh, app.mc, app.Cph, app.Cpc)
            var Cmax = calc_Cmax(app.mh, app.mc, app.Cph, app.Cpc)
            var Cr = calc_Cr(app.mh, app.mc, app.Cph, app.Cpc)
            var Cc = app.mc * app.Cpc
            var Ch = app.mh * app.Cph
            var NTU = NTU_from_UA(UA, Cmin)
            var eff = effectiveness_from_NTU(NTU, Cr)

            // Solve for radiator and heat exchanger combination
            app.radiator_output = 0
            for (var i = 0; i < 100; i++) {
                app.Thi = app.Tho + app.heatpump_output / (app.Cph * app.mh)

                let Q = eff * Cmin * (app.Thi - app.Tci)

                app.Tco = app.Tci + Q / (Cc)
                app.Tho = app.Thi - Q / (Ch)

                let MWT = (app.Tco + app.Tci) / 2

                let last_heat_output = app.radiator_output
                app.radiator_output = app.rated_output * Math.pow(((MWT - app.room) / app.rated_dT), 1.3)

                app.Tci = app.Tco - (app.radiator_output / (app.Cpc * app.mc))

                if (Math.round(app.radiator_output * 100) == Math.round(last_heat_output * 100) && Math.round(Q * 100) == Math.round(app.radiator_output * 100)) {
                    break;
                }
            }

            if (app.cop_method == "ecodan") {
                app.heatpump_COP = get_ecodan_cop(app.Thi, app.outside, app.radiator_output / 5000)
                app.heatpump_COP_compare = get_ecodan_cop(app.Tco, app.outside, app.radiator_output / 5000)
            } else {
                app.heatpump_COP = heatpump_COP(app.Thi, app.outside)
                app.heatpump_COP_compare = heatpump_COP(app.Tco, app.outside)
            }

            // Search for heat output from radiators at given flow temperature and flow rate
            app.Tho_direct = app.Thi
            var MWT = app.Thi
            app.radiator_output_compare = 0
            for (var i = 0; i < 100; i++) {
                MWT = (app.Thi + app.Tho_direct) * 0.5
                let last_heat_output = app.radiator_output_compare
                app.radiator_output_compare = app.rated_output * Math.pow(((MWT - app.room) / app.rated_dT), 1.3)
                dT = app.radiator_output_compare / (app.Cph * app.mh)
                app.Tho_direct = app.Thi - dT
                if (Math.round(app.radiator_output_compare * 100) == Math.round(last_heat_output * 100)) {
                    break;
                }
            }
        }
    },
    filters: {
        toFixed: function(val, dp) {
            if (isNaN(val)) {
                return val;
            } else {
                return val.toFixed(dp)
            }
        }
    }
});

app.update();

function calc_Cmin(mh, mc, Cph, Cpc) {
    Ch = mh * Cph
    Cc = mc * Cpc
    return Math.min(Ch, Cc)
}

function calc_Cmax(mh, mc, Cph, Cpc) {
    Ch = mh * Cph
    Cc = mc * Cpc
    return Math.max(Ch, Cc)
}

function calc_Cr(mh, mc, Cph, Cpc) {
    Ch = mh * Cph
    Cc = mc * Cpc
    Cmin = Math.min(Ch, Cc)
    Cmax = Math.max(Ch, Cc)
    return Cmin / Cmax
}

function NTU_from_UA(UA, Cmin) {
    return UA / Cmin
}

function effectiveness_from_NTU(NTU, Cr) {
    if (Cr < 1) {
        return (1.0 - Math.exp(-NTU * (1.0 - Cr))) / (1.0 - Cr * Math.exp(-NTU * (1.0 - Cr)))
    } else if (Cr == 1) {
        return NTU / (1.0 + NTU)
    } else {
        return false
    }
}

function heatpump_COP(flowT, outsideT) {
    T_condensing = flowT + 4
    T_refrigerant = outsideT - 6
    Carnot_COP = (T_condensing + 273) / ((T_condensing + 273) - (T_refrigerant + 273))
    Practical_COP = 0.5 * Carnot_COP
    return Practical_COP
}