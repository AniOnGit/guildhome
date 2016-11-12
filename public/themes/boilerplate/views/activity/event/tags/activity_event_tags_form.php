Tags <a href="#" onclick="toggle('tags');">(+/-)</a>
<form method="post" action="{##form_action##}">
    <fieldset id="tags">
        <legend>Event Tags</legend>
        <input type="checkbox" {##activity_tags_checked##} name="activity[tags]" value="1" /> Allow Tags<br />
        <input type="submit" name="activity[submit][tags]" value="{##submit_text##}" />
    </fieldset>
</form>