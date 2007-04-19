/**
 * Main JS package for the application.
 */
Cuckoo = function() {

    var Evt  = YAHOO.util.Event;
    var Dom  = YAHOO.util.Dom;
    var Conn = YAHOO.util.Connect;
    
    return {

        DEBUG: true,

        /**
         *
         */
        init: function() {

            // Catchall block for things to be done upon page load.
            Evt.on(window, 'load', function() {
            
                this.initLogger();

            }, this, true);

            return this;
        },

        /**
         * Provide a common way to handle logging in context
         */
        LOG_ID: "yui_log",
        initLogger: function() {
            // Inject a logging div, if needed.
            if (this.DEBUG && !this.log_reader) {
                if (!Dom.get(this.LOG_ID))
                    document.body.appendChild( DIV({'id':this.LOG_ID}));
                this.log_reader = new YAHOO.widget.LogReader(this.LOG_ID);
                this.log_reader.hide();

                YAHOO.widget.Logger.enableBrowserConsole();

                this.log = Cuckoo.getLogger("Main");
                this.log("init");
            }
        },
        getLogger: function(cat) {
            return function(msg, lvl) {
                YAHOO.log(msg, (lvl || "debug"), "cuckoo:"+cat);
            }.bind(this);
        },

        EOF: null

    };

}().init();
