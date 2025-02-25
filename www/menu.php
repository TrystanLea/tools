<?php

$menu = array(
    "simpleheatloss" => array(
        "category" => "Heat Loss",
        "case" => "SimpleHeatLoss",
        "title" => "Super Simple Heat Loss",
        "description" => "Explore the difference between custom measured assumptions and those typically used from the CIBSE domestic heating design guide."
    ),
    "weathercomp" => array(
        "case" => "WeatherComp",
        "title" => "Weather Compensation",
        "description" => "Calculate optimum weather compensation settings for a heat pump"
    ),
    "scop" => array(
        "category" => "Performance Calculation",
        "case" => "SCOP",
        "title" => "Heat Pump System Performance Calculator",
        "description" => "Calculate heat pump SCOP based on design flow temperature"
    ),
    "dynamic_heatpump_v1" => array(
        "category" => "Dynamic Simulation",
        "case" => "dynamic_heatpump_v1",
        "title" => "Dynamic heat pump simulator",
        "description" => "Explore continuous vs intermittent heating, temperature set-backs and schedules"
    ),
    "solarmatching" => array(
        "category" => "Electric Supply",
        "case" => "SolarMatching",
        "title" => "Explore Solar Matching",
        "description" => "Explore how much home electric + heat pump demand can be met by solar and a battery"
    ),
    "hex1" => array(
        "category" => "Hydraulic Separation",
        "case" => "HEX1",
        "title" => "Plate heat exchanger, fixed flow temperature",
        "description" => "Calculate heat output and COP for a system with a counterflow plate heat exchanger between the heat pump and the radiator system. Fixed flow temperature version."
    ),
    "hex2" => array(
        "category" => "Hydraulic Separation",
        "case" => "HEX2",
        "title" => "Plate heat exchanger, fixed heat transfer",
        "description" => "Calculate heat output and COP for a system with a counterflow plate heat exchanger between the heat pump and the radiator system. Improved fixed heat output version."
    ),
    "llh" => array(
        "category" => "Hydraulic Separation",
        "case" => "llh",
        "title" => "Low loss header",
        "description" => "Calculate heat output and COP for a system with a low loss header between the heat pump and the radiator system",
    ),
    "volume_sim" => array(
        "category" => "Dynamic Simulation",
        "case" => "volume_sim",
        "title" => "Volume Simulator",
        "description" => "Explore system volume, starts per hour and cycling"
    ),
    "volume_sim_cop" => array(
        "category" => "Dynamic Simulation",
        "case" => "volume_sim_cop",
        "title" => "Volume simulator with heat pump COP",
        "description" => "Explore heat pump minimum modulation and cycling's effect of COP"
    ),
    "pressureloss" => array(
        "case" => "PressureLoss",
        "title" => "Pipe pressure loss calculator",
        "description" => "Calculates pressure loss using the Darcy Weisbach equation with the friction factor derived using the Newton-Raphson method to solve the Colebrook-White equation."
    ),
    "mis031" => array(
        "category" => "Performance Calculation",
        "case" => "MIS031",
        "title" => "MIS031: Heat Pump System Performance Estimate",
        "description" => ""
    ),
    "co2_sim" => array(
        "case" => "co2_sim",
        "title" => "Building indoor CO2 simulator",
        "description" => "Explore effect of building occupancy & air change rate on indoor CO2 concentrations."
    ),
    "storagesimulator" => array(
        "category" => "Electric Supply",
        "case" => "StorageSimulator",
        "title" => "Storage simulator",
        "description" => "Explore how much home electric + heat pump demand can be met by different mixes of wind, solar, nuclear, battery storage, long duration energy storage or other final backup supply."
    ),
    "ukgridsim" => array(
        "category" => "Electric Supply",
        "case" => "UKGridSim",
        "title" => "UK Grid Simulator",
        "description" => "Can you match supply and demand on the UK grid?"
    ),
    "lcoe" => array(
        "case" => "LCOE",
        "title" => "LCOE",
        "description" => "LCOE",
        "hide" => true
    ),
    "lcoe_simple" => array(
        "case" => "LCOE_Simple",
        "title" => "LCOE Simple",
        "description" => "LCOE ...",
        "hide" => true
    ),
);