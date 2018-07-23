$(function() {
	"use strict";

	$('#content').artEditor({
		imgTar: '#imageUpload',
		limitSize: 5,   // 兆
		showServer: true,
		uploadUrl: 'service.php',
		data: {},
		dataType:'html',
		uploadField: 'image',
		placeholader: '<p>请输入文章正文内容</p>',
		validHtml: ["br"],
		uploadSuccess: function(path) {
			return path;
		},
		uploadError: function(res) {
			// something error
			console.log(res);
		}
	});
});
