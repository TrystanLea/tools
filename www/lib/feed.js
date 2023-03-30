var feed = {
    /*
    * Get feed data from emoncms.org
    * @param {string} feedid - Feed id
    * @param {string} start - Start date
    * @param {string} end - End date
    * @param {string} interval - Interval
    * @param {string} average - Average
    * @param {function} callback - Callback function
    * @return {object} - Feed data
    */
    getdata: function (feedid,start,end,interval,average,callback) 
    {
        // Load outside temperature data from emoncms.org
        start = new Date(start).getTime() / 1000;
        end = new Date(end).getTime() / 1000;
        // round to nearest hour
        start = Math.floor(start / 3600) * 3600;
        end = Math.floor(end / 3600) * 3600;

        // axios get parameters as array
        axios.get('api.php', {
            params: {
                ids: feedid,
                start: start,
                end: end,
                interval: interval,
                average: average,
                skipmissing: 0,
                limitinterval: 0,
                timeformat: 'notime'
            }
        })
        .then(response => {
            // expected response format
            // response.data = [{"feedid": 1, "data": [100,100,100]}]
            var series = response.data;

            // Convert fixed interval data to time series
            // reduces bandwidth when loading data
            for (var s = 0; s < series.length; s++) {
                var data = [];
                for (var i = 0; i < series[s].data.length; i++) {
                    var time = start + (i * interval);
                    data.push([time * 1000, series[s].data[i]]);
                }
                series[s].data = data;
            }
            callback(series);
        })
        .catch(error => {
            console.log(error);
        });
    }   
}