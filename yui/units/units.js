YUI.add('moodle-block_course_level-units', function(Y) {

    var UNITSNAME = 'blocks_course_units';

    var UNITS = function() {
        UNITS.superclass.constructor.apply(this, arguments);
    };

    Y.extend(UNITS, Y.Base, {

        event:null,
        overlayevent:null,
        overlays: [], //all the comment boxes

        initializer : function(params) {

            //attach a show event on the div with id = units
            for (var i=0;i<this.get('unitids').length;i++)
            {
                var unitid = this.get('unitids')[i];
                var dlgContent = Y.one('#unitoverlay-'+unitid+' .contentBox').get('innerHTML');
                var dlgTitle = Y.one('#unitoverlay-'+unitid+' .dlgTitle').get('innerHTML');
                this.overlays[unitid] = new M.core.dialogue({
                    headerContent: dlgTitle,
                    bodyContent: dlgContent,
                    visible: false, //by default it is not displayed
                    lightbox : true,
                    zIndex:1000,
                    height: '350px'
                });

                // Remove the dialog contents. If Javascript isn't enabled then this script won't run and the
                // list of units will be left there.
                Y.one('#unitoverlay-'+unitid+' .contentBox').remove();

                // Replace the dialog title with a link. If Javascript isn't loaded then it will be left as a title.
                Y.one('#unitoverlay-'+unitid+' .dlgTitle').remove();
                var element = document.createElement("a");
                element.setAttribute("href", "#"); // Don't link anywhere.
                element.setAttribute("id", "#units-"+unitid); // Give it a unique id so we can attach a 'click' event to it.
                element.setAttribute("class", "dlgTitle");
                element.innerHTML = dlgTitle;
                Y.one('#unitoverlay-'+unitid).appendChild(element);

                // Render and hide the new dialog.
                this.overlays[unitid].render();
                this.overlays[unitid].hide();

                Y.one('#unitoverlay-'+unitid+' .dlgTitle').on('click', this.show, this, unitid);
            }

        },

        show : function (e, unitid) {

            //hide all overlays
            for (var i=0;i<this.get('unitids').length;i++)
            {
                this.hide(e, this.get('unitids')[i]);
            }

            this.overlays[unitid].show(); //show the overlay

            e.halt(); // we are going to attach a new 'hide overlay' event to the body,
            // because javascript always propagate event to parent tag,
            // we need to tell Yahoo to stop to call the event on parent tag
            // otherwise the hide event will be call right away.

            //we add a new event on the body in order to hide the overlay for the next click
            this.event = Y.one(document.body).on('click', this.hide, this, unitid);
            //we add a new event on the overlay in order to hide the overlay for the next click (touch device)
            this.overlayevent = Y.one("#unitoverlay-"+unitid).on('click', this.hide, this, unitid);
        },

        hide : function (e, unitid) {
            this.overlays[unitid].hide(); //hide the overlay
            if (this.event != null) {
                this.event.detach(); //we need to detach the body hide event
                //Note: it would work without but create js warning everytime
                //we click on the body
            }
            if (this.overlayevent != null) {
                this.overlayevent.detach(); //we need to detach the overlay hide event
                //Note: it would work without but create js warning everytime
                //we click on the body
            }

        }

    }, {
        NAME : UNITSNAME,
        ATTRS : {
            unitids: {}
        }
    });

    M.blocks_course_level = M.blocks_course_level || {};
    M.blocks_course_level.init_units = function(params) {
        return new UNITS(params);
    }

}, '@VERSION@', {
    requires:['base','overlay', 'moodle-enrol-notification']
});