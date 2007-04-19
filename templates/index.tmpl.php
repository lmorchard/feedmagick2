<?php include $this->template('_header.tmpl.php'); ?>

<h2>Index</h2>

<h3>Pipelines</h3>

<ul>

</ul>

<h3>Modules</h3>

<ul>
    <?php foreach ($this->modules as $module=>$meta): ?>
        <li>
            <h4><?php $this->eprint($meta['title'] . ' ' .$meta['version']) ?></h4>
            <p><?php $this->eprint($meta['description']) ?></p>
        </li>
    <?php endforeach ?>
</ul>

<?php $this->startOutputCapture('sidebar') ?>
    <li>
        <?php // var_dump($this->modules) ?>
    </li>
<?php $this->endOutputCapture('sidebar') ?>

<?php include $this->template('_footer.tmpl.php'); ?>
