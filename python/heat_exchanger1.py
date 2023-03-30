from math import exp

# Heat exchanger code from ht library

# from ht import effectiveness_NTU_method
# from pprint import pprint
# pprint(effectiveness_NTU_method(mh=0.77, mc=0.08, Cph=4200., Cpc=4200., subtype='counterflow', Tci=5, Thi=60, Tho=54.8))

def calc_Cmin(mh, mc, Cph, Cpc):
    Ch = mh*Cph
    Cc = mc*Cpc
    return min(Ch, Cc)
    
def calc_Cmax(mh, mc, Cph, Cpc):    
    Ch = mh*Cph
    Cc = mc*Cpc
    return max(Ch, Cc)
    
def calc_Cr(mh, mc, Cph, Cpc):    
    Ch = mh*Cph
    Cc = mc*Cpc
    Cmin = min(Ch, Cc)
    Cmax = max(Ch, Cc)
    return Cmin/Cmax

def NTU_from_UA(UA, Cmin):
    return UA/Cmin
     
def effectiveness_from_NTU(NTU, Cr):
    if Cr < 1:
        return (1. - exp(-NTU*(1. - Cr)))/(1. - Cr*exp(-NTU*(1. - Cr)))
    elif Cr == 1:
        return NTU/(1. + NTU)
    else:
        return False

def heatpump_COP(flowT,outsideT):
    T_condensing = flowT + 4
    T_refrigerant = outsideT - 6
    Carnot_COP = (T_condensing + 273) / ((T_condensing+273) - (T_refrigerant + 273))
    Practical_COP = 0.5 * Carnot_COP
    return Practical_COP


rad_heat50k = 15000   
room = 20.      
mh = 0.2
mc = 0.2
Cph = 4020.
Cpc = 4187.
Tci = room
Thi = 44.5
UA = 1200.

T_ambient = 0
                            
Cmin = calc_Cmin(mh=mh, mc=mc, Cph=Cph, Cpc=Cpc)
Cmax = calc_Cmax(mh=mh, mc=mc, Cph=Cph, Cpc=Cpc)
Cr = calc_Cr(mh=mh, mc=mc, Cph=Cph, Cpc=Cpc)
Cc = mc*Cpc
Ch = mh*Cph
NTU = NTU_from_UA(UA=UA, Cmin=Cmin)
eff = effectiveness_from_NTU(NTU=NTU, Cr=Cr)

# Solve for radiator and heat exchanger combination
heat_output = 0
for i in range(0,100):
  
  Q = eff*Cmin*(Thi - Tci)
  Tco = Tci + Q/(Cc)
  Tho = Thi - Q/(Ch)
  
  MWT = (Tco+Tci)/2
  last_heat_output = heat_output
  heat_output = rad_heat50k * pow(((MWT - room) / 50),1.3)

  Tci = Tco - (heat_output/(Cpc*mc))
  
  if int(heat_output)==int(last_heat_output) and int(Q)==int(heat_output):
    break

COP = heatpump_COP(Thi,T_ambient)

print ("---------------")
print ("HP COP %0.1f" % COP)
print ("Thi: %0.1f" % Thi)
print ("Tho: %0.1f" % Tho)
print ("Tco: %0.1f" % Tco)
print ("Tci: %0.1f" % Tci)
print ("H %dW" % heat_output)

# heat_output = 2700
dT = pow(heat_output/rad_heat50k,1/1.3) * 50
MWT = room + dT
T_flow = MWT + heat_output / (2*Cph*mh)

COP = heatpump_COP(T_flow,T_ambient)

print ("---------------")
print ("HP COP %0.1f @ flowT %0.1f" % (COP,T_flow))

# Search for heat output from radiators at given flow temperature and flow rate
Tho = Thi
MWT = Thi
heat_output = 0
for i in range(0,100):
  MWT = (Thi + Tho)*0.5
  last_heat_output = heat_output
  heat_output = rad_heat50k * pow(((MWT - room) / 50),1.3)
  dT = heat_output / (Cph*mh)
  Tho = Thi - dT
  if int(heat_output)==int(last_heat_output):
    break
    
print ("or heat output at:") 
print ("Thi: %0.1f" % Thi)
print ("Tho: %0.1f" % Tho)
print ("H @ %d" % heat_output)
  
  













