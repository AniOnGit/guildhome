<section class="form">
    <form method="post" action="{##form_action##}">
        <fieldset>
            <legend>{##page_slug##}</legend>
            <label for="activity_content">{##page_content_text##}</label>
            <textarea id="activity_content" name="page[content]" placeholder="{##page_content_text##}">{##page_content##}</textarea>
            {##page_content_validation##}
            <p>You can use <a href="https://en.wikipedia.org/wiki/Markdown">Markdown</a> to spice up your pages if you like.</p>
            <input type="submit" name="page[preview]" value="{##preview_text##}" />
            <input type="submit" name="page[submit]" value="{##submit_text##}" />
        </fieldset>
        {##page_general_validation##}
    </form>
</section>