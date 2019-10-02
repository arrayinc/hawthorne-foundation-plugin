<div class="wrap">
    <h1>Scholarship Settings</h1>
    <form action="options.php" method="post">
        <?php settings_fields("array-settings");?>
        <h2>Spring Semester</h2>
        <label for="spring_application_date_start">Application Start Date</label>
        <p><input type="date" id="spring_application_date_start" name="spring_application_date_start" value="<?php echo get_option("spring_application_date_start"); ?>"></p>
        <label for="spring_application_date_end">Application End Date</label>
        <p><input type="date"  id="spring_application_date_end" name="spring_application_date_end" value="<?php echo get_option("spring_application_date_end"); ?>"></p>
        <h2>Fall Semester</h2>
        <label for="fall_application_date_start">Application Start Date</label>
        <p><input type="date"  id="fall_application_date_start" name="fall_application_date_start" value="<?php echo get_option("fall_application_date_start"); ?>"></p>
        <label for="fall_application_date_end">Application end Date</label>
        <p><input type="date"  id="fall_application_date_end" name="fall_application_date_end" value="<?php echo get_option("fall_application_date_end"); ?>"></p>
        <label for="application_message">Message</label>
            <p><textarea id="application_message" name="application_message" rows="10" cols="80"><?php echo get_option("application_message"); ?></textarea></p>
        </label>
        <h2>Create New Applicant Settings</h2>
        <?php gravity_forms_selects(); ?>
        <?php submit_button();?>
    </form>
</div>