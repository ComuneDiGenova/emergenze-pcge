<?php
    $len=count($eventi_attivi);
    
    if($len==1) {   
?>
        <select readonly="" class="form-control"  name="evento" required>
            <?php 
                for ($i=0;$i<$len;$i++){
                $nota0 = trim((string)($nota_eventi_attivi[0][1] ?? ''));
                $id0   = $tipo_eventi_attivi[0][0];
                $tipo0 = $tipo_eventi_attivi[0][1];
                
                $label0 = $nota0.' (id=' . $id0 . ', tipo=' . $tipo0 . ')';

                echo '<option name="evento" value="'. $tipo_eventi_attivi[0][0] .'">' . $label0 . '</option>';
                }
            ?>
        </select>
        <small id="eventohelp" class="form-text text-muted">Un solo evento attivo.</small>   
            <?php 
                } else {
            ?>

            <select class="form-control"  name="evento" required>
                <option value=''>Seleziona un evento tra quelli attivi </option>
                    <?php 
                        for ($i=0;$i<$len;$i++){
                        $nota = trim((string)($nota_eventi_attivi[$i][1] ?? ''));
                        $id   = $tipo_eventi_attivi[$i][0];
                        $tipo = $tipo_eventi_attivi[$i][1];

                        $label = $nota.' (id=' . $id . ', tipo=' . $tipo . ')';

                        echo '<option name="evento" value="' . $tipo_eventi_attivi[$i][0] . '">' . $label . '</option>';
                        }
                    ?>
            </select>

            <?php
            }
            ?>
