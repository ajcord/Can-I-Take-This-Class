
    <footer>
        <div class="container">
            <p class="small text-center">
                Availability data collected daily from the
                <a href="https://courses.illinois.edu/cisdocs/explorer">Course Explorer API</a>.
                <br>
                The data and predictions provided by this website
                are not guaranteed to be correct or up to date and
                are not a substitute for meeting with an academic advisor.
            </p>
        </div>
    </footer>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      <? if (substr($_SERVER["HTTP_HOST"], 0, 3) == "dev"): ?>
        ga('create', 'UA-72483548-2', 'auto');
      <? else: ?>
        ga('create', 'UA-72483548-1', 'auto');
      <? endif ?>
      ga('send', 'pageview');
    </script>
</body>
</html>