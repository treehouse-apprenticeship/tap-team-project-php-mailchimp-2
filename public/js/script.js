$(document).ready(function() {
  $('.tag-select').select2({
    placeholder: 'Enter a tag name and follow with a comma or space...',
    allowClear: true,
    tags: true,
    tokenSeparators: [',', ' ']
  });
});