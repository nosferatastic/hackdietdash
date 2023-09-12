## HackDash

<p>This is a work-in-progress project in which I am aiming to replicate the functionality of <a href="https://trendweight.com">TrendWeight</a>, a website that I use frequently, while making adjustments to better fit my needs.</p>

<p>With that in mind, the core of the application syncs with the Fitbit API to enable automatic retrieval of weight data on a daily basis. This is then plotted onto a graph with a trendline based on the principles of the <a href="https://www.fourmilab.ch/hackdiet/">Hacker's Diet</a>.

<p><strong>Please mind the mess.</strong> While I continue on this project, I'm developing my understanding of React.JS and so things will be frequently cleaned up and code adjusted to adhere more strongly to established principles. There will be bugs and unresolved quirks, for the time being.</p>

<p>This project is being built with:</p>

<ul>
    <li>Laravel Framework & Laravel Herd</li>
    <li>PHP (currently 8.3)</li>
    <li>React.JS</li>
    <li>Semantic UI React</li>
    <li>Material React</li>
    <li>Recharts</li>
</ul>

## Ongoing work 

<p>This will be added to as time goes on, I'm sure:</p>

<ul>
    <li>Enable updating of existing weight data/adding of new weight data from within the weight listings index.</li>
    <li>Better error handling of malformed Fitbit API Authorisation bits & pieces.</li>
    <li>Google-authenticated SSO.</li>
    <li>Integration of additional data points (body fat).</li>
    <li>Changing of units (for those who weight in KG, for example).</li>
    <li>Initialisation of user state and onboarding (set start date, etc.)</li>
</ul>

## Screenshots

<p>Since it's unlikely others will be getting this running at this early stage, and it is not published, here are some screenshots:</p>

# Weight Dashboard View - Long-Term (180 Days)

![Weight dashboard view - long-term (180 days)](/../working/resources/img/screenshot1.png?raw=true "Weight Dashboard View - Long-Term (180 Days)")

# Weight Dashboard View - Short-Term (14 Days)

![Weight dashboard view - short-term (14 days)](/../working/resources/img/screenshot4.png?raw=true "Weight Dashboard View - Short-Term (14 Days)")

# Weight Index View - WIP

![Weight index view (WIP)](/../working/resources/img/screenshot4.png?raw=true "Weight Index View (WIP)")

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
