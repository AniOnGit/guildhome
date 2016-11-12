{##activity_message##}
{##activity_loop##}
<article class="activity{##css##}">
    <h2>Activity Event</h2>
    <header class="activity_details">
        <img src="{##avatar##}" width="40" height="40" alt="avatar of user {##activity_identity##}" />
        on {##activity_published##},<br />
        <strong>{##activity_identity##}</strong> posted <strong>{##activity_type##}</strong>.
    </header>
    <section class="content clearfix">
        <section class="details">
            <h2>{##details_link##}<a href="{##details_link_url##}">{##details_link_text##}</a>{/##details_link##}</h2>
            <time datetime="{##activity_event_datetime##}">Date: {##activity_event_date##}</time>
            <section class="signups clearfix">
                <h3>Signups</h3>
                <span title="{##activity_signups_list##}">{##activity_signups##}</span>
                {##activity_detailed_signups_list##}
                {##activity_tags##}
            </section>
            <section class="description clearfix">
                <h3>Description</h3>
                {##activity_content##}{##link_more##}<a href="{##link_more_url##}" class="more">{##link_more_text##}</a>{/##link_more##}
            </section>
            <section class="signup_form clearfix">
                <h3>Signup now</h3>
                {##activity_signup_form##}
            </section>
        </section>
    </section>
    <footer>
        {##activity_not_logged_in##}
        <section class="not_logged_in">
            {##comment_link##}<a href="{##comment_link_url##}">{##comment_link_text##}</a>{/##comment_link##}
        </section>
        {/##activity_not_logged_in##}
        {##activity_logged_in##}
        <section class="logged_in">
            {##update_link##}<a href="{##update_link_url##}">{##update_link_text##}</a>{/##update_link##} | {##delete_link##}<a href="{##delete_link_url##}">{##delete_link_text##}</a>{/##delete_link##}
        </section>
        {/##activity_logged_in##}
    </footer>
</article>
{/##activity_loop##}
