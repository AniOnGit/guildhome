<section class="widget">
    {##widget_title##}<h3>{##widget_title_text##}</h3>{/##widget_title##}
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Date</th>
            <th>Link</th>
        </tr>
        </thead>
        <tbody>
{##signup_loop##}
        <tr>
            <td>{##activity_title##}</td>
            <td class="center"><time datetime="{##activity_event_datetime##}">{##activity_event_date##} {##activity_event_time##}</time></td>
            <td class="center">{##activity_details_link##}<a href="{##activity_details_link_url##}">{##activity_details_link_text##}</a>{/##activity_details_link##}</td>
        </tr>
{/##signup_loop##}
        </tbody>
    </table>
</section>
