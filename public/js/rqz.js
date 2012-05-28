function showExplanation(identifier) {
	hint = $("#question-explanation-details-" + identifier).html();
	$("#question-hint-box").html( hint );
	$("#question-hint-box").show();
}