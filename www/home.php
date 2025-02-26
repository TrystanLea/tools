<div class="container" style="max-width:800px">

    <div class="row">
        <div class="col">
            <br>
            <h3>Heat pump calculators</h3>
            <p>Open source tools to help with heat pump design and understanding.</p>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col">

            <?php foreach ($menu as $key => $value) : ?>
                <?php if (isset($value['hide']) && $value['hide'] === true) continue; ?>
                <h5><a href="<?php echo $value['case']; ?>"><?php echo $value['title']; ?></a></h5>
                <p><?php echo $value['description']; ?></p>
                <hr>
            <?php endforeach; ?>

            <h3>Other tools</h3>
            <p>The following are developed by OpenEnergyMonitor but are not integrated into this site:</p>

            <h5><a href="https://openenergymonitor.org/heatlossjs">HeatLoss.js</a></h5>
            <p>An open source room by room heat loss calculator based on BS EN 12831:2003. Includes automated internal
                heat balance & room temperature solver. Save and open projects to your computer locally.<br><i>Source
                    code available on github <a href="https://github.com/trystanLea/heatlossjs">here</a>.</b></i></p>            

            <hr>
            <h5><a href="https://openenergymonitor.org/sapjs">SAP.js</a></h5>
            <p>An open source javascript implementation of the SAP 2012 monthly building energy model. This tool builds
                on previous work on a similar tool called <a
                    href="https://github.com/emoncms/MyHomeEnergyPlanner">MyHomeEnergyPlanner</a>, which started as a
                collaboration between OpenEnergyMonitor and CarbonCoop, both share the same core SAP model <a
                    href="https://github.com/trystanlea/Openbem">OpenBEM</a>. This tool is just a new user interface
                that focuses on input flexibility as well as pairing things right down to the basics.<br><i>Source code
                    available on github <a href="https://github.com/trystanLea/SAPjs">here</a>.</i></b></p>
        
            <hr>

            <h5><a href="https://openenergymonitor.org/zcem">ZeroCarbonBritain energy model</a></h5>
            <p>An open-source javascript implementation of the 10-year hourly cross sectoral UK energy model behind the ZeroCarbonBritain scenario. This model can be used to explore a wide variety of scenarios. Try creating your own scenario that gets to zero carbon!</p>

        </div>
    </div>
</div>