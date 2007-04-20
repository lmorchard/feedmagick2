<?php 
    $this->page_id = 'index';
    $this->has_sidebar = FALSE; 
?>
<?php include $this->template('_header.tmpl.php'); ?>

    <h2>What is FeedMagick2</h2>

    <p>
        FeedMagick2 is a toolkit for filtering, sorting, blending, converting,
        mungeing, and tweaking syndication feeds in formats like RSS and Atom.
        It provides relatively simple modules that can be strung together in 
        pipelines to process feeds on the web or command line.
    </p>

    <?php $this->startOutputCapture('sidebar'); ?>
        <li>
            <h3>Side thing</h3>
            <p>sd fgasdf asdf asdf a</p>
        </li>
    <?php $this->endOutputCapture('sidebar'); ?>

    <h3>Pipelines</h3>

    <p>
        This installation offers the following pipelines for feed 
        processing:
    </p>

    <ul>

    </ul>

    <h3>Modules</h3>

    <p>
        For building pipelines, this installation offers the following 
        processing modules:
    </p>

    <ul class="modules">
        <?php foreach ($this->modules as $module=>$meta): ?>
            <li>
                <h4><?php $this->eprint($meta['title'] . ' ' .$meta['version']) ?></h4>
                <p><?php $this->eprint($meta['description']) ?></p>
            </li>
        <?php endforeach ?>
    </ul>

<?php include $this->template('_footer.tmpl.php'); ?>
