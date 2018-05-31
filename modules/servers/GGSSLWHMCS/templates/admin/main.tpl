<div class="mg-wrapper body" data-target=".body" data-spy="scroll" data-twttr-rendered="true" id="MGNextIsWHMCSConfig">
    <!--
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600&subset=all" rel="stylesheet" type="text/css"/>
     
    <link rel="stylesheet" type="text/css" href="{$assetsURL}/css/simple-line-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{$assetsURL}/css/uniform.default.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{$assetsURL}/css/components-rounded.css" rel="stylesheet">
    -->
    <link rel="stylesheet" type="text/css" href="{$assetsURL}/css/font-awesome.css" />
    <link rel="stylesheet" type="text/css" href="{$assetsURL}/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{$assetsURL}/css/jquery.dataTables.css" />
    <link rel="stylesheet" type="text/css" href="{$assetsURL}/css/select2.css" />
    <link rel="stylesheet" type="text/css" href="{$assetsURL}/css/onoffswitch.css" />
    <link rel="stylesheet" type="text/css" href="{$assetsURL}/css/mg-style.css" rel="stylesheet">  
    <script type="text/javascript" src="{$assetsURL}/js/whmcsProdConfSupp.js"></script>
    <script type="text/javascript" src="{$assetsURL}/js/mgLibs.js"></script>
    <script type="text/javascript" src="{$assetsURL}/js/bootstrap.js"></script>
    <script type="text/javascript" src="{$assetsURL}/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="{$assetsURL}/js/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="{$assetsURL}/js/select2.min.js"></script>
    <script type="text/javascript" src="{$assetsURL}/js/bootstrap-hover-dropdown.min.js"></script>
    <script type="text/javascript">
        JSONParser.create('{$mainJSONURL}');
    </script>
    <div class="full-screen-module-container">
        <div class="row">
            <nav class="navbar navbar-default" role="navigation">
              <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <a class="navbar-brand" href="{$mainURL}">
                        {$mainName}
                    </a>
                </div>
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    {if $menu}
                        <ul class="nav navbar-nav">
                            {foreach from=$menu key=catName item=category}
                                {if $category.submenu}
                                    <li class="menu-dropdown">
                                        <a href="{$category.url}" data-hover="dropdown" data-close-others="true">
                                            {if $category.icon}<i class="{$category.icon}"></i>{/if}
                                            {if $category.label}
                                                {$subpage.label}
                                            {else}
                                                {$MGLANG->T('pagesLabels',$catName,'label')}
                                            {/if}
                                            <i class="fa fa-angle-down dropdown-angle"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-left">
                                            {foreach from=$category.submenu key=subCatName item=subCategory}
                                                <li>
                                                    <a href="{$subCategory.url}">
                                                        {if $subCategory.icon}<i class="{$subCategory.icon}"></i>{/if} 
                                                        {if $subCategory.label}
                                                            {$subCategory.label}
                                                        {else}
                                                            {$MGLANG->T('pagesLabels',$catName,$subCatName)}
                                                        {/if}
                                                    </a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </li>
                                {else}
                                    <li>
                                        <a href="{$category.url}">
                                            {if $category.icon}<i class="{$category.icon}"></i>{/if} 
                                            {if $category.label}
                                                {$subpage.label}
                                            {else}
                                                {$MGLANG->T('pagesLabels',$catName,'label')}
                                            {/if}
                                        </a>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    {/if}
                    {if $searchAction}
                      <form class="navbar-form navbar-left" role="search" method="post" action="{$searchAction}">
                        <div class="form-group">
                          <input name="searchMigration" type="text" class="form-control" placeholder="{$MGLANG->T('searchMigration')}" value="{$smarty.post.searchMigration}">
                        </div>
                        <button type="submit" class="btn btn-default"><i style="font-size:20px" class="glyphicon glyphicon-search"></i></button>
                      </form>
                    {/if}
                    <div>
                        <a alt="ModulesGarden Custom Development" target="_blank" href="http://www.modulesgarden.com" class="slogan nblue-box">
                            <span class="mg-logo"></span>
                            <small>We are here to help you, just click!</small>
                        </a>
                    </div>
                </div><!-- /.navbar-collapse -->
              </div><!-- /.container-fluid -->
            </nav>
        </div>      
        <div class="row" id="MGAlerts">
                {if $error}
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                        <p><strong>{$error}</strong></p>
                    </div>
                {/if}
                {if $success}
                    <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                        <p><strong>{$success}</strong></p>
                    </div>
                {/if}
                <div style="display:none;" data-prototype="error">
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                        <strong></strong>
                        <a style="display:none;" class="errorID" href=""></a>
                    </div>
                </div>
                <div style="display:none;" data-prototype="success">
                    <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only"></span></button>
                        <strong></strong>
                    </div>
                </div>
        </div>
        <div class="row">
            {$content}
        </div>
        <div id="MGLoader" style="display:none;" >
            <div>
                <img src="{$assetsURL}/img/ajax-loader.gif" alt="Loading ..." />
            </div>
        </div>   
    </div>
</div>
