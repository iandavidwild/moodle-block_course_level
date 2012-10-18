YUI.add('moodle-block_course_level-courses', function(Y) {

    var COURSESNAME = 'blocks_course_courses';

    var COURSES = function() {
        COURSES.superclass.constructor.apply(this, arguments);
    };

    Y.extend(COURSES, Y.Base, {

        event:null,
        overlayevent:null,
        overlays: [], //all the comment boxes

        initializer : function(params) {

            //attach a show event on the div with id = courses
            for (var i=0;i<this.get('courseids').length;i++)
            {
                var courseid = this.get('courseids')[i];
                var dlgContent = Y.one('#courseoverlay-'+courseid+' .contentBox').get('innerHTML');
                var dlgTitle = Y.one('#courseoverlay-'+courseid+' .dlgTitle').get('innerHTML');
                this.overlays[courseid] = new M.core.dialogue({
                    headerContent: dlgTitle,
                    bodyContent: dlgContent,
                    visible: false, //by default it is not displayed
                    lightbox : true,
                    zIndex:1000,
                    height: '350px'
                });

                // Remove the dialog contents. If Javascript isn't enabled then this script won't run and the
                // list of courses will be left there.
                Y.one('#courseoverlay-'+courseid+' .contentBox').remove();

                // Replace the dialog title with a link. If Javascript isn't loaded then it will be left as a title.
                Y.one('#courseoverlay-'+courseid+' .dlgTitle').remove();
                var element = document.createElement("a");
                element.setAttribute("href", "#"); // Don't link anywhere.
                element.setAttribute("id", "#courses-"+courseid); // Give it a unique id so we can attach a 'click' event to it.
                element.setAttribute("class", "dlgTitle");
                element.innerHTML = dlgTitle;
                Y.one('#courseoverlay-'+courseid).appendChild(element);

                // Render and hide the new dialog.
                this.overlays[courseid].render();
                this.overlays[courseid].hide();

                Y.one('#courseoverlay-'+courseid+' .dlgTitle').on('click', this.show, this, courseid);
            }

        },

        show : function (e, courseid) {

            //hide all overlays
            for (var i=0;i<this.get('courseids').length;i++)
            {
                this.hide(e, this.get('courseids')[i]);
            }

            this.overlays[courseid].show(); //show the overlay

            e.halt(); // we are going to attach a new 'hide overlay' event to the body,
            // because javascript always propagate event to parent tag,
            // we need to tell Yahoo to stop to call the event on parent tag
            // otherwise the hide event will be call right away.

            //we add a new event on the body in order to hide the overlay for the next click
            this.event = Y.one(document.body).on('click', this.hide, this, courseid);
            //we add a new event on the overlay in order to hide the overlay for the next click (touch device)
            this.overlayevent = Y.one("#courseoverlay-"+courseid).on('click', this.hide, this, courseid);
        },

        hide : function (e, courseid) {
            this.overlays[courseid].hide(); //hide the overlay
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
        NAME : COURSESNAME,
        ATTRS : {
            courseids: {}
        }
    });

    M.blocks_course_level = M.blocks_course_level || {};
    M.blocks_course_level.init_courses = function(params) {
        return new COURSES(params);
    }

}, '@VERSION@', {
    requires:['base','overlay', 'moodle-enrol-notification']
});