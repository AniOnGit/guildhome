<section class="widget">
    {##widget_title##}<h3>{##widget_title_text##}</h3>{/##widget_title##}
    <ul>
{##event_loop##}
        <li class="event">
            <a href="{##activity_details_link##}">
                <span>{##activity_title##}</span> : <time datetime="{##activity_event_date##} {##activity_event_time##}">{##activity_event_date##} @ {##activity_event_time##}</time>
            </a>
        </li>
{/##event_loop##}
    </ul>
</section>