/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	config.language =  'en';
	config.filebrowserImageUploadUrl = ImageUploadUrl; 
	config.image_previewText=' ';
	config.height = '400';
	config.resize_maxHeight	= '1600';
	config.resize_maxWidth  = '900';
	config.resize_minWidth  = '500';
        config.extraPlugins = 'youtube,images';
        config.allowedContent = true;

    config.protectedSource.push( /<ins[\s\S]*?ins>/g );
	config.toolbar  = [
	{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
	{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
	{ name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },

	{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
	{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
	
	{ name: 'insert', items: [ 'Image','Images','Youtube', 'Table', 'HorizontalRule'] },
	{ name: 'document', items: [ 'Source','Maximize' ] }

	];
};
