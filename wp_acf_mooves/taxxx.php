          <!-- 
          тут чо значит
          в acf cтавим 
          внутри повторителя
          тип поля:таксономия
          назв поля:link или другое
          таксономия:категория
          сохранение
          обьект термина
          внешний вид - флажок, после всех заполнений - выбрать или сразу выбрать :D
          -->
           <?php if (get_field('cat_list', $tax)) { ?>
                <div class="banner__categories">
                    <?php while (have_rows('cat_list', $tax)) { the_row(); 
                        $term = get_sub_field('link'); 
                        if ($term) {
                            $term_link = get_term_link($term);
                            ?>
                            <a href="<?php echo esc_url($term_link); ?>" class="banner__categories_item">
                                <?php echo esc_html($term->name); ?>
                            </a>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>