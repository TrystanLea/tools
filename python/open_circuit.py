def heatpump_COP(flowT,outsideT):
    T_condensing = flowT + 4
    T_refrigerant = outsideT - 6
    Carnot_COP = (T_condensing + 273) / ((T_condensing+273) - (T_refrigerant + 273))
    Practical_COP = 0.5 * Carnot_COP
    return Practical_COP

outside = -3
room = 20
heat_output = 4000
specific_heat = 4200
flow_rate = 12 / 60
rad_heat50k = 15000

dT = pow(heat_output / rad_heat50k,1/1.3)*50
MWT = room + dT
flowT = MWT + heat_output / (2 * specific_heat * flow_rate)
returnT = MWT - heat_output / (2 * specific_heat * flow_rate)
COP = heatpump_COP(flowT,outside)

print ("Heat output:\t\t%d W" % round(heat_output))
print ("Flow temperature:\t%0.1f°C" % flowT)
print ("Return temperature:\t%0.1f°C" % returnT)
print ("COP:\t\t\t%0.2f" % COP)
