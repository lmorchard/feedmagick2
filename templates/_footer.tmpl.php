                    </div>
                </div>
                <?php if ( isset($this->has_sidebar) && $this->has_sidebar ): ?>
                    <div id="side" class="yui-b">
                        <?php include $this->template('_sidebar.tmpl.php'); ?>
                    </div>
                <?php endif ?>
            </div>
            <div id="ft">
                <p></p>
            </div>  
        </div> 
    </body> 
</html>
