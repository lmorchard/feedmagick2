<?php 
    $this->page_id = 'index';
    $this->has_sidebar = FALSE; 
?>
<?php include $this->template('_header.tmpl.php'); ?>

    <h2>What is FeedMagick2</h2>

    <p>
        FeedMagick2 is a toolkit for filtering, sorting, blending, converting, 
        munging, and tweaking syndication feeds in RSS and Atom as well as XHTML content and 
        other XML formats.  It provides relatively simple <a href="#sectmodules">modules</a> 
        that can be strung together in <a href="#sectpipelines">pipelines</a> to 
        process feeds on the web or command line.
    </p>

    <h3>Starting points</h3>
    <p>
        This installation of FeedMagick2 comes installed with some pipelines 
        and modules to use and explore.
    </p>
    <ul>
        <li><a href="#sectpipelines">Pipelines</a> - Installed pipelines for processing feeds</li>
        <li><a href="#sectmodules">Modules</a> - Documentation on individual filter units available for use in pipelines</li>
    </ul>

    <div id="sectpipelines">
        <h3>Pipelines</h3>
        <p>
            This installation offers the following pipelines for feed 
            processing:
        </p>
        <ul class="pipelines">
            <?php foreach ($this->pipelines as $name=>$meta): ?>
                <li>
                    <h4><a href="<?php echo $this->BASE_URL ?>/inspect/<?php $this->eprint($name) ?>"><?php $this->eprint($meta['title']) ?></a></h4>
                    <p><?php $this->eprint($meta['description']) ?></p>
                </li>
            <?php endforeach ?>
        </ul>
    </div>

    <div id="sectmodules">
        <h3>Modules</h3>
        <p>
            For building pipelines, this installation offers the following 
            processing modules:
        </p>
        <ul class="modules">
            <?php foreach ($this->modules as $module=>$meta): ?>
                <li>
                    <h4><?php $this->eprint($meta['title']) ?></h4>
                    <p><?php $this->eprint($meta['description']) ?></p>
                </li>
            <?php endforeach ?>
        </ul>
    </div>

<?php include $this->template('_footer.tmpl.php'); ?>
