function imageModal_open(previewImageControl) {
	$(".w3-modal.image-modal img.w3-modal-content").attr("src", $(previewImageControl).attr("src").replace(".preview.", "."));
	$(".w3-modal.image-modal").show();
}