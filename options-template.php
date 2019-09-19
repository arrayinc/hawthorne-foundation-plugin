<div class="wrap">
    <h1>Array Settings</h1>
    <form action="options.php" method="post">
        <?php settings_fields("array-settings");?>
        <label for="application_date_start">Application Start Date</label>
        <p><input type="date" id="application_date_start" name="application_date_start" value="<?php echo get_option("application_date_start"); ?>"></p>
        <label for="application_date_end">Application End Date</label>
        <p><input type="date"  id="application_date_end" name="application_date_end" value="<?php echo get_option("application_date_end"); ?>"></p>
        <label for="application_message">Message</label>
            <p><textarea id="application_message" name="application_message" rows="10" cols="80"><?php echo get_option("application_message"); ?></textarea></p>
        </label>
        <?php submit_button();?>
    </form>
</div>