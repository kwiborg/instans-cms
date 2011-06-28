<h1>Import af nyhedsbrev-modtagere og/eller brugere</h1>
<form method="post" enctype="multipart/form-data" action="">
<?php if ($_GET[usermessage_error]) { ?>
    <div class="usermessage_error"><?=utf8_decode($_GET[usermessage_error])?></div>
<?php } ?>

<?php if (!$_GET[step] || $_GET[step] == 1) { ?>
<div class="feltblok_header">Trin 1: Valg af import-fil</div>
<div class="feltblok_wrapper">
    <h2>Vælg fil i CSV-format</h2>
    <input type="file" size="70" name="importfile" id="importfile" />
    <p class="feltkommentar">
    Bemærk, at filen skal overholde følgende specifikationer: Data skal være semikolon-separeret, og der skal ikke være nogen tekst-delimiter (dvs. ingen "" rundt om navne osv). 
    <br/><br/>
    Desuden skal filen overholde følgende format: Første linje skal indeholde kolonnenavne. Kolonnerne skal have følgende navne og komme i denne rækkefølge:
    <br/><br/>
    cfield___FIRSTNAME<br/>
    cfield___LASTNAME<br/>
    cfield___EMAIL<br/>
    cfield___COMPANY<br/>
    cfield___ADDRESS<br/>
    cfield___ZIPCODE<br/>
    cfield___CITY<br/>
    cfield___PHONE<br/>
    cfield___CELLPHONE<br/>
    cfield___JOB_TITLE<br/> 
    <br/>
    Navnene på kolonnerne forklarer, hvilket indhold, der skal være i kolonnerne. Det er OK at lade visse kolonner være tomme for data, men kolonne-titlen skal stadig være med. Fornavn, Efternavn og E-mail kan IKKE udelades.
    <br/><br/><a href="modules/newsletterimport/newsletterimport_example.xls">Klik her for at se et eksempel på en gyldig Excel-fil.</a><br/><a href="modules/newsletterimport/newsletterimport_example.csv">Klik her for at se et eksempel på en gyldig CSV-fil baseret på ovenstående Excel-fil.</a>
    <br/><br/>
    BEMÆRK: Det er tilladt at have personens fulde navn (f.eks. Hans Peter Hansen) i feltet "cfield_FIRSTNAME". Hvis dette er tilfældet i din importfil, opdager systemet det automatisk. Husk, at sammenlægningen af for- og efternavn skal gælde for HELE filen (og ikke kun enkelte rækker).
    <br/><br/>
    Filen checkes, før den importeres, så en ugyldig fil vil <b>ikke</b> resultere i en fejlimport.
    </p>
    <div class="knapbar">
        <input type="button" value="Indlæs fil" onclick="this.form.submit()" /> 
    </div>
</div>
<?php } ?>

<?php if ($_GET[step] == 2) { ?>
<div class="usermessage_ok">Filformatet er i orden.</div>
<div class="feltblok_header">Trin 2: Indstillinger for import</div>
<div class="feltblok_wrapper">
    <!--
    <h2>Fornavn og efternavn i samme felt</h2>
    <input type="checkbox" name="import_fullname" />&nbsp;Ja, i min fil står personens fulde navn i fornavn-feltet (cfield___FIRSTNAME), og efternavn-feltet (cfield___LASTNAME) er derfor tomt.
    -->
    <h2>Vælg hvilken handling, du vil udføre</h2>
    <input type="hidden" name="dothis" value="run_import" />
    <input type="hidden" name="impmode_res" id="impmode_res" value="" />
    <input type="radio" name="importmode" value="1" onclick="impmode(this.value)" />&nbsp;Jeg vil importere abonnenter til et eksisterende nyhedsbrev<br/>
    <input type="radio" name="importmode" value="2" onclick="impmode(this.value)" />&nbsp;Jeg vil importere brugere ind i systemet (til en eksisterende eller ny brugergruppe)<br/>
    <input type="radio" name="importmode" value="3" onclick="impmode(this.value)" />&nbsp;Jeg vil BÅDE importere brugere ind i systemet og SAMTIDIG gøre dem til abonnenter på et eksisterende nyhedsbrev<br/>

    <h2>Vælg hvilket nyhedsbrev, du vil importere til</h2>
    <select disabled name="imptemplate" id="imptemplate">
        <option value="0">Vælg nyhedsbrev...</option>
        <?php
            $sql = "select ID, TITLE from NEWSLETTER_TEMPLATES where DELETED='0' and SITE_ID in (0, $_SESSION[SELECTED_SITE])";
            $res = mysql_query($sql);
            while ($row = mysql_fetch_assoc($res)){
                $html .= "<option value='$row[ID]'>$row[TITLE]</option>\n";
            }
            echo $html;
        ?>        
    </select>

    <h2>Vælg hvilken brugergruppe, du vil importere til</h2>
    <script>var gnames = Array();</script>
    <select disabled name="impgroup" id="impgroup" onchange="gchange(this.value)">   
        <option value="0">Vælg eksisterende gruppe...</option>
        <?php
            $html = "";
            $sql = "select ID, GROUP_NAME from GROUPS where DELETED='0' and UNFINISHED='0' and SITE_ID in (0, $_SESSION[SELECTED_SITE])";
            $res = mysql_query($sql);
            while ($row = mysql_fetch_assoc($res)){
                $html .= "<option value='$row[ID]'>$row[GROUP_NAME]</option>\n";
                $js .= "gnames[gnames.length] = \"".addslashes($row[GROUP_NAME])."\"".";\n";
            }
            echo $html;
        ?>        
    </select>
    eller opret i stedet en ny gruppe med navnet:
    <input disabled type="text" name="import_newgroupname" id="import_newgroupname" onkeypress="ngchange(this.value)" onblur="ngout(this.value)" />

    <div class="knapbar">
        <input type="button" value="Afbryd" onclick="location='index.php?content_identifier=newsletterimport&step=1&df=<?=$_GET[f]?>'" />
        <input type="button" value="Gennemfør import" onclick="verify_import();" />
    </div>
</div>
<?php } ?>

<?php if ($_GET[step] == 3) { ?>
<div class="feltblok_header">Trin 3: Importen blev gennemført</div>
<div class="feltblok_wrapper">
    <?php 
        if ($_GET[m] == 1){
            echo "<a href='index.php?content_identifier=newslettersubscribers&template_id=$_GET[tid]'>Klik her for at se de importerede abonnenter til nyhedsbrevet</a>.";
        }
        if ($_GET[m] == 2){
            echo "<a href='index.php?content_identifier=groups&dothis=medlemmer&id=$_GET[gid]'>Klik her for at se de importerede brugere</a>.";
        }
        if ($_GET[m] == 3){
            echo "<a href='index.php?content_identifier=newslettersubscribers&template_id=$_GET[tid]'>Klik her for at se de importerede abonnenter til nyhedsbrevet</a>.<br/>";
            echo "<a href='index.php?content_identifier=groups&dothis=medlemmer&id=$_GET[gid]'>Klik her for at se de importerede brugere</a>.";
        }
    ?>
    <?php
        $messages = explode("|||SPLIT|||", file_get_contents($_SERVER[DOCUMENT_ROOT]."/cms/modules/newsletterimport/temp/res_$_GET[f].csv"));
    ?>
    <div style="margin-top:20px; height:100px; overflow:auto; background-color:#080; color:#fff">
        OK:<br/><br/><?=$messages[0]?>
    </div>
    <div style="margin-top:20px; height:100px; overflow:auto; background-color:#800; color:#fff">
        FEJL:<br/><br/>        
        <?=$messages[1]?>
    </div>
</div>
<?php } ?>


</form>



<script type="text/javascript">
    <?=$js?>
</script>


