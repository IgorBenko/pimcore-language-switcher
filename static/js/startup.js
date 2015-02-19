pimcore.registerNS("pimcore.plugin.languageswitcher");

pimcore.plugin.languageswitcher = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.languageswitcher";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){

    },

    postOpenDocument: function(document, documentType) {
        var self = this;
        var tab = pimcore.globalmanager.get("document_" + document.id);
        var toolbar = tab.toolbar;

        var docs = null;
        jQuery.ajax({
            url: "/plugin/LanguageSwitcher/index/get-docs-in-other-branches",
            dataType: "json",
            type: "GET",
            data: {
                id: document.id
            },
            success: function(data) {
                var btnMenu = [];

                var docIds = [];
                for (var i=0; i<data.length; i++) {
                    var docId = data[i]["id"];
                    docIds.push(docId);
                    btnMenu.push({
                        text: data[i]["label"] + "<span class=\"ls_menu_lang\"> [" + data[i]["language"] + "]</em>",
                        iconCls: "pimcore_icon_language_" + data[i]["language"],
                        handler: self.openDocuments.createDelegate(self, [[docId]])
                    });
                }

                btnMenu.unshift({
                    text: t('Open all'),
                    handler: self.openDocuments.createDelegate(self, [docIds])
                });

                tab.toolbarButtons.switchBranch = new Ext.SplitButton({
                    text: t('Switch branch'),
                    iconCls: "pimcore_icon_langueageswitcher_medium",
                    scale: "medium",
                    handler: function() {
                        this.showMenu();
                    },
                    menu: btnMenu
                });


                tab.toolbar.insert(tab.toolbar.items.length-3, tab.toolbarButtons.switchBranch);
                tab.toolbar.doLayout();
            }
        });
    },

    openDocuments: function(ids) {
        for (var i=0; i<ids.length; i++) {
            pimcore.helpers.openDocument(ids[i], "page");
        }
    }
});

var languageswitcherPlugin = new pimcore.plugin.languageswitcher();