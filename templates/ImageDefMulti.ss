<% if $HeroImages.Count > 1 %>
    <% include SlickPicture Items=$HeroImages %>
<% else %>
    <% loop $HeroImages %>
        <picture>
            <!--[if IE 9]><video style="display: none;"><![endif]-->
            <% loop $Images %>
                <source media="$String" srcset="$Image.URL">
                <% if Last %>
                    <!--[if IE 9]></video><![endif]-->
                    <img src="$Image.URL" alt="$Up.ImageTitle">
                <% end_if %>
            <% end_loop %>
        </picture>
    <% end_loop %>
<% end_if %>
