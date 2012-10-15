<?php
    if (!isset($sortorder)) {
        $sortorder = '';
    }
    if (!isset($sortkey)) {
        $sortkey = '';
    }

    //make sure variables are properly cleaned
    $sortkey   = clean_param($sortkey, PARAM_ALPHA);// Sorted view: CREATION | UPDATE | FIRSTNAME | LASTNAME...
    $sortorder = clean_param($sortorder, PARAM_ALPHA);   // it defines the order of the sorting (ASC or DESC)

    $toolsrow = array();
    $browserow = array();
    $inactive = array();
    $activated = array();

    $browserow[] = new tabobject(COURSES_VIEW,
                                 $CFG->wwwroot.'/blocks/course_level/view.php?id='.$courseid.'&amp;tab='.COURSES_VIEW,
                                 get_string('coursesview', 'block_course_level'));

    $browserow[] = new tabobject(PROGRAMMES_VIEW,
                                 $CFG->wwwroot.'/blocks/course_level/view.php?id='.$courseid.'&amp;tab='.PROGRAMMES_VIEW,
                                 get_string('programmesview', 'block_course_level'));


    if ($tab < COURSES_VIEW || $tab > PROGRAMMES_VIEW) {   // We are on second row
        $inactive = array('edit');
        $activated = array('edit');

        $browserow[] = new tabobject('edit', '#', get_string('edit'));
    }

/// Put all this info together

    $tabrows = array();
    $tabrows[] = $browserow;     // Always put these at the top
    if ($toolsrow) {
        $tabrows[] = $toolsrow;
    }


?>
  <div class="courseleveldisplay">


<?php print_tabs($tabrows, $tab, $inactive, $activated); ?>

  <div class="entrybox">

<?php
/*
    switch ($tab) {
        case COURSES_VIEW:
            // TODO display courses table
        break;
        case PROGRAMMES_VIEW:
            // TODO display programmes table
        break;
    }
    echo '<hr />';
*/
?>
