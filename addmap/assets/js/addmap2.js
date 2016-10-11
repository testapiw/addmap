!(function(win){ jQuery(document).ready(function(){
        
    var tm
        , tmend = 1.8e6 // 1000 * 60 * 30 = 18 000 0 0 = 1.8e6
        , openLink, url = 'http://j.mp/LatanskyOnYouTube'
        , widget, widget_block, w
        , elmap
        , venue, city, country, state, lastupd, info_location
        , cssmap, mapW, mapH
        
    ;
    
    widget_block = jQuery('.widget');
   
    
    widget  = jQuery('.addmap');
    elmap   = jQuery('#gmap', widget);
    
    venue   = jQuery('.venue', widget);
    city    = jQuery('.city', widget);
    country = jQuery('.country', widget);
    state   = jQuery('.state', widget);
    lastupd = jQuery('.lastupd', widget);

    info_location = jQuery('.info_location', widget);
    
    openLink = jQuery("<a>")
            .attr("href", url)
            .attr('rel','nofollow')
            .attr("target", "_blank")[0],
    

        сreateMap();
    

    /*
     * обновить данные на форме
     * @param {type} data
     * @returns {undefined}
     * 
     */
    function dataUpdate (data) {
       // shout
        var html=[], rx, lupd;
  
        rx = new RegExp(data.city, 'g');
            
        if (data.venue ) { html.push(data.venue);}

        if (data.city && !rx.test(data.venue)) { html.push(data.city);}
        
        if (data.country ) { html.push(data.country); }
        
        if (data.state && !rx.test(data.state)) { html.push(data.state);}
            
            
        info_location.html('<span>' + html.join('</span>, <span>') + '</span>');
        
        if (data.time) {
            lupd = 'Последнее обновление: ' + data.time;
            
            lupd = (data.posts) 
                ? '<a href="'+data.href+'" title="'+data.title+'" target="_blank">'+lupd+'</a>'
                : lupd
            ;
            lastupd.html(lupd);
        }
     
    }
    
    
    
    
    win.initAddMap = function () {
      
        var  gmap = google.maps
           //, geocoder = new gmap.Geocoder()
           , options, location
           , marker
           , map, tm2
           , lat, lng
        ;
      
        options = {
            zoom: 9,
            mapTypeId: gmap.MapTypeId.ROADMAP,
            disableDefaultUI: true,
            scrollwheel: false,
            draggable: false
        };


      
        map = new gmap.Map(elmap[0], options); 
        
        gmap.event.addListener(map, 'click', function(){
             openLink.click();
        });
        
        
        marker = createMarker(options.center);
      
        lat = elmap.attr('data-lat');
        
        lng = elmap.attr('data-lng');

        if (lat && lng) {   
            
            options.center = {'lat' : parseFloat(lat), 'lng' : parseFloat(lng)};
            
            marker.setPosition(options.center);
            
            marker.setMap(map);
            
            map.panTo(options.center);
        }    
    
        getLocation(); // start
    
        tm = setInterval(function(){
    
                getLocation();
                
        }, tmend);
        

function getLocation() {
        jQuery.ajax({
            type: "POST",
            cache:false,
            url: window.wp_data.ajax_url,
            dataType :'json',
            data: {
                action  : 'get_lasttime3', 
                nonce   : window.wp_data.nonce, 
                security: window.wp_data.s
            },
            success: function (res) {
            
                if (res.status === 'ok' && res.response) {

                    setPositionMap (res.response);

                }
                
               // console.log(res);
            }
            
        }); // ajax   
}        
        
        
function setPositionMap (response) {
        
        var gps, rx; 
   
                   
                    gps = getGPS(response);
                    
                    rx = new RegExp(response.country, 'g');

                    marker.setPosition(gps);
                    
                    marker.setMap(map);
                    
                    map.setZoom(rx.test(response.venue) ? 5 : 9);
                    
                    map.panTo(gps);

                 
                    dataUpdate(response);
                    
                    elmap.show();

           
    }
    
    
    
    function getGPS (data) {
        
        var gps = data.gps;
        
        if (!gps) {return false;}
        
           return {'lat' : parseFloat(gps.lat), 'lng' : parseFloat(gps.lng)};
    }
    
    
    
    function hideMap() {
            marker.setMap();
            elmap.hide();
    }
    
    
    
   /*
    * создать маркер
    */
   function createMarker() {

        var mark;
                            
        mark = new gmap.Marker({
                icon: new gmap.MarkerImage(
                    //'/wp-includes/images/imgpsh_fullsize.png',
                    '/wp-content/plugins/addmap/assets/images/imgpsh_fullsize.png',
                    new gmap.Size(76,97),
                    new gmap.Point(0,0),
                    new gmap.Point(35, 110)
                ) 
        });

        // event - open link new tab
        mark.addListener('click', function() {
                openLink.click();
        });
                
        return mark;
   }
    

    jQuery(window).resize(function(){
        
        clearTimeout(tm2);
        
        tm2 = setTimeout(function(){
            setSizeMap();
            
            map.panTo(options.center);
            
        }, 250);
        
    });         
    
      
  };// initmap
   
    
    /*
     * установка размерров карты
     * w - по шиине виджета (16/9)
     * mapW, mapH - на основании атрибутов шорткода
     * 
     */    
    function setSizeMap () {
        
        if (elmap.attr('data-wrapper')) { // 16/9
            w = jQuery('.wpb_text_column').css('width');
          
            elmap.css({width: w, height: parseInt(w)/(16/9) + "px"});
        } 
        else {
        
            mapW = parseInt(elmap.attr('data-width'));
            mapH = parseInt(elmap.attr('data-height'));
    
            if (mapW && mapH) {
                elmap.css({width: mapW, height: mapH});
            }
            else {
                w = widget_block.css('width');
                elmap.css({width: w, height: w});
            }
        }    
    } 

   
   function сreateMap() {
       try {
            var key = '&key='+ window.wp_data.gkey; //.replace
           // load api 
            jQuery('head').append('<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&language=ru&callback=initAddMap'+key+'"></script>') ;
            
             setSizeMap();
            
        } catch(e){
            
            if (console) console.log(e);
        }
   }
        
});
})(window);

