<?php 
    $this->page_id = 'help';
    $this->has_sidebar = TRUE;
?>
<?php include $this->template('_header.tmpl.php'); ?>
    
    <?php echo $this->content ?>

    <?php $this->startOutputCapture('sidebar'); ?>
        <li>
            <h3>Table of Contents</h3>
            <ul class="toc">
                <?php foreach ($this->toc as $title=>$link): ?>
                    <?php if ($this->help_id == $link): ?>
                        <li class="current"><?php $this->eprint($title) ?></li>
                    <?php else: ?>
                        <li><a href="<?php echo $this->BASE_URL.'/help/'.$link ?>"><?php $this->eprint($title) ?></a></li>
                    <?php endif ?>
                <?php endforeach ?>
            </ul>
        </li>
    <?php $this->endOutputCapture('sidebar'); ?>

<?php include $this->template('_footer.tmpl.php'); ?>
