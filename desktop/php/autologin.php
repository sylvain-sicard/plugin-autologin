<?php
if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('autologin');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

function sortByOption($a, $b) {
  return strcmp($a['name'], $b['name']);
}

?>
<style>
  div.autologin span.masked {
    font-family: "text-security-disc" !important;
  }
</style>
<div class="row row-overflow">

  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
    <!-- Boutons de gestion du plugin -->
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle"></i>
        <br>
        <span>{{Ajouter}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br>
        <span>{{Configuration}}</span>
      </div>
    </div>
    <legend><i class="fas fa-table"></i> {{Mes AutoLogin}}</legend>
    <?php
    if (count($eqLogics) == 0) {
      echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement trouvé, cliquer sur "Ajouter" pour commencer}}</div>';
    } else {
      // Champ de recherche
      echo '<div class="input-group" style="margin:5px;">';
      echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
      echo '<div class="input-group-btn">';
      echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
      echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
      echo '</div>';
      echo '</div>';
      // Liste des équipements du plugin
      echo '<div class="eqLogicThumbnailContainer">';
      foreach ($eqLogics as $eqLogic) {
        $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
        echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
        echo '<img src="' . $eqLogic->getImage() . '"/>';
        echo '<br>';
        echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
        echo '<span class="hiddenAsCard displayTableRight hidden">';
        echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
        echo '</span>';
        echo '</div>';
      }
      echo '</div>';
    }
    ?>
  </div>

  <div class="col-xs-12 eqLogic" style="display: none;">
    <div class="input-group pull-right" style="display:inline-flex;">
      <span class="input-group-btn">
        <!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
        <a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
        </a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
        </a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
        </a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
        </a>
      </span>
    </div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
    </ul>
    <div class="tab-content">
      <!-- Onglet de configuration de l'équipement -->
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <form class="form-horizontal">
          <fieldset>
            <div class="col-lg-6">
              <legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Nom de l'équipement}}</label>
                <div class="col-sm-4">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Objet parent}}</label>
                <div class="col-sm-4">
                  <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                    <option value="">{{Aucun}}</option>
                    <?php
                    $options = '';
                    foreach ((jeeObject::buildTree(null, false)) as $object) {
                      $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                    }
                    echo $options;
                    ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Catégorie}}</label>
                <div class="col-sm-8">
                  <?php
                  foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" >' . $value['name'];
                    echo '</label>';
                  }
                  ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Options}}</label>
                <div class="col-sm-4">
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
                </div>
              </div>

              <legend><i class="fas fa-cogs"></i> {{Paramètres spécifiques}}</legend>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{IP Autorisée}}</label>
                <div class="col-sm-4">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ip" placeholder="{{eg: 192.168.X.Y}}" />
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Utilisateur}}</label>
                <div class="col-sm-4">
                  <select id="select_gcastplayer" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="user">
                    <option value="">{{Selectionner un utilisateur (non admin)}}</option>
                    <?php
                    if ($eqLogic) {
                      foreach (user::byEnable(true) as $user) {
                        if ($user->getProfils() != 'admin') {
                          echo '<option value="' . $user->getLogin() . '">' . $user->getLogin() . '</option>';
                        }
                      }
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">{{Page Jeedom}}</label>
                <div class="col-sm-8">
                  <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="redirecturl" placeholder="eg : index.php" />
                </div>
              </div>
               <div class="form-group">
                <label class="col-sm-4 control-label">{{Auto redirect}}</label>
                <div class="col-sm-8">
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr"  data-l1key="configuration" data-l2key="autoredirect" checked>{{Activer}}</label>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <legend><i class="fas fa-at"></i> {{URL à appeler}}</legend>
              <div class="form-group">
                <label class="col-sm-2 control-label">{{Accès interne}}</label>
                <div class="alert alert-info col-sm-10 autologin">
                  <a class="btn btn-default btn-xs maskToggle pull-right"><i class="fas fa-eye"></i></a>
                  <?php echo '<span class="mask masked">' . network::getNetworkAccess('internal') . '</span>/plugins/autologin/core/php/go.php?apikey=<span class="mask masked">' . jeedom::getApiKey('autologin') . '</span>&id=<span class="eqLogic_id"/>'; ?></span>
                </div>
                <label class="col-sm-2 control-label">{{Accès externe}}</label>
                <div class="alert alert-info col-sm-10 autologin">
                  <a class="btn btn-default btn-xs maskToggle pull-right"><i class="fas fa-eye"></i></a>
                  <?php echo '<span class="mask masked">' . network::getNetworkAccess('external') . '</span>/plugins/autologin/core/php/go.php?apikey=<span class="mask masked">' . jeedom::getApiKey('autologin') . '</span>&id=<span class="eqLogic_id"/>'; ?></span>
                </div>
              </div>
            </div>

          </fieldset>
        </form>
      </div>

    </div>
  </div>
</div>

<?php
include_file('desktop', 'autologin', 'js', 'autologin');
include_file('core', 'plugin.template', 'js');
?>
