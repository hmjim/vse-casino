(function() {
	tinymce.create('tinymce.plugins.rndm', {
		init : function(ed, url) {
			ed.addButton('screen', {
				title : 'Добавить скриншоты',
				image : url+'/../img/icon-screen.png',
				onclick : function() {
					ed.selection.setContent('[screen]');
				}
			});
		},
		createControl : function(n, cm) {
			return null;
		},
	});
	tinymce.PluginManager.add('rndm', tinymce.plugins.rndm);
})();