Signups <a href="#" onclick="toggle('signups');">(+/-)</a>
<form method="post" action="{##form_action##}">
    <fieldset id="signups">
        <legend>Event signups</legend>
        <input type="checkbox" {##activity_signups_checked##} name="activity[signups]" value="1" /> Allow signups<br />
        <input {##activity_signups_min_checked##} type="checkbox" name="activity[signups_min]" value="1" /> Minimum signups for event to happen
        <input type="number" name="activity[signups_min_val]" value="{##signups_min_val##}" /><br />
        <input {##activity_signups_max_checked##} type="checkbox" name="activity[signups_max]" value="1" /> Maximum signups for this event
        <input type="number" name="activity[signups_max_val]" value="{##signups_max_val##}" /><br />
        <input {##activity_keep_signups_open_checked##} type="checkbox" name="activity[keep_signups_open]" value="1" /> Keep signups open beyond set limit<br />
        <input type="submit" name="activity[submit][signups]" value="{##submit_text##}" />
    </fieldset>
</form>