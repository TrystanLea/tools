var section = {}
var section_heat = {}
var section_flowrate = {}

section_heat['primaries'] = 3911;
section['primaries'] = [
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':100 },
    {'direction': 'flow', 'type': 'elbow 22mm' },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':200 },
    {'direction': 'flow', 'type': 'elbow 22mm' },
    {'direction': 'flow', 'type': 'flexi hose 22mm', 'length':500 },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':200 },
    {'direction': 'flow', 'type': 'elbow 22mm' },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':600 },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':1200 },
    {'direction': 'flow', 'type': 'coupler 22mm' },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':1500 },
    {'direction': 'flow', 'type': 'elbow 22mm' },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':200 },
    {'direction': 'flow', 'type': 'spirotech dearator' },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':80 },
    {'direction': 'flow', 'type': 'tee 22mm' },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':330 },
    {'direction': 'flow', 'type': 'ball valve 22mm' },
    {'direction': 'flow', 'type': 'elbow 22mm' },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':50 },
    {'direction': 'flow', 'type': 'sontex superstatic 440 3.5m3' },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':140 },
    {'direction': 'flow', 'type': 'tee 22mm' }
];

section_heat['bathroom_to_bed1'] = 3469;
section['bathroom_to_bed1'] = [
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':500 },
    {'direction': 'flow', 'type': 'mpos diverter valve' },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':150 },
    {'direction': 'flow', 'type': 'elbow 22mm' },
    {'direction': 'flow', 'type': 'copper pipe 22mm', 'length':1400 },
    {'direction': 'flow', 'type': 'tee 22mm' }
];

// Calculate flow rate in section
section_flowrate['primaries'] = section_heat['primaries'] / (4150*5);

var target_pressure_drop = 5.0;

for (var z=0; z<100; z++) {

    let dp = calc();
    if (dp < target_pressure_drop-0.1) {
        section_flowrate['primaries'] += (target_pressure_drop - dp)*0.01;
    } else {
        break;
    }
}

console.log('Flowrate: ' + (section_flowrate['primaries']*3.6) + ' m3/hr');

function calc() {

    // Convert flowrate to m3/hr
    var flowrate = section_flowrate['primaries'];
    var flowrate_m3hr = flowrate * 3.6;

    // Calculate pressure losses
    var pressure_drop = 0;

    for (var i = 0; i < section['primaries'].length; i++) {
        var part = section['primaries'][i];
        var dp = null;

        // Copper pipe 22mm
        if (part.type == 'copper pipe 22mm') {
            var length = part['length'] * 0.001;
            dp = copper_pipe_22mm (flowrate, length);
        }

        // Flexi hose 22mm
        if (part.type == 'flexi hose 22mm') {
            var length = part['length'] * 0.001;
            dp = copper_pipe_22mm (flowrate, length);        
        }

        // Elbow 22mm
        if (part.type == 'elbow 22mm') {
            dp = copper_pipe_22mm (flowrate) * 0.8;
        }

        // Coupler 22mm
        if (part.type == 'coupler 22mm') {
            dp = copper_pipe_22mm (flowrate) * 0.8;
        }

        // Tee 22mm
        if (part.type == 'tee 22mm') {
            dp = copper_pipe_22mm (flowrate) * 1.0;
        }

        // Ball valve 22mm
        if (part.type == 'ball valve 22mm') {
            dp = copper_pipe_22mm (flowrate) * 1.0;
        }

        // Spirotech dearator
        if (part.type == 'spirotech dearator') {
            let kv = 9.15 // m3/hr at 1 bar
            dp = Math.pow(flowrate_m3hr/kv, 2) * 10.2
        }

        // Sontex superstatic 440 3.5m3
        if (part.type == 'sontex superstatic 440 3.5m3') {
            let kv = 8.75 // m3/hr at 1 bar
            dp = Math.pow(flowrate_m3hr/kv, 2) * 10.2
        }
        
        pressure_drop += dp;
        part.pressure_drop = dp;
    }
    // console.log(pressure_drop)
    return pressure_drop;
}

function print_pressure_drops(section_name) {

    // Print all pressure drops
    for (var i = 0; i < section[section_name].length; i++) {
        var part = section[section_name][i];
        let dp = part.pressure_drop;
        if (dp != null) {
            dp = dp.toFixed(3);
        } else {
            dp = 'N/A';
        }
        
        console.log(part.type + '\t' + dp + ' mh')
    }
}

function copper_pipe_22mm(flowrate,length = 1) {
    return (0.4295 * Math.pow(flowrate, 2) + (0.0726 * flowrate)) * length;
}