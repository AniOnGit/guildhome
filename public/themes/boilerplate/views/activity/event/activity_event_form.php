<script type="text/javascript">
function toggle(div){
    if (document.getElementById(div).className == 'hidden') {
        document.getElementById(div).className = '';
    } else {
        document.getElementById(div).className = 'hidden';
    }
}
</script>
<section class="form">
    <p>Together we are strong!</p>
    <form method="post" action="{##form_action##}">
        {##activity_event_content_saved##}
        <fieldset>
            <legend>General event settings</legend>
            <input type="text" name="activity[title]" placeholder="{##activity_title_text##}" value="{##activity_title##}" /> {##activity_title_validation##}<br />
            <textarea id="activity_content" name="activity[content]" placeholder="{##activity_content_text##}">{##activity_content##}</textarea>
            {##activity_content_validation##}<br />
            Date: <input type="date" name="activity[date]" placeholder="Date" value="{##activity_date##}" />
            Time: <input type="time" name="activity[time]" placeholder="Time" value="{##activity_time##}" /> {##activity_date_validation##}<br />
            <input type="checkbox" {##activity_comments_checked##} name="activity[comments]" value="1" /> Allow comments for this event<br />
        </fieldset>
        {##activity_general_validation##}
        <fieldset>
            <legend>Post itttt!</legend>
            <input type="submit" name="activity[submit]" value="{##submit_text##}" />
            <input type="submit" name="activity[preview]" value="{##preview_text##}" /><br />
            <span>You can set advanced options like Signups and Role/Class selection after a initial save. This is a necessary evil at the moment, thank you for your understanding.</span>
        </fieldset>
    </form>
    {##signups_form##}
    {##tags_form##}
</section>