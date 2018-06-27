/**
 * This js file is to capture the XAPI statements and then send it to controller which in turn saves it in the database.
 */
document.addEventListener('DOMContentLoaded', function() {              //to run the function when DOM(document object model) has been loaded
	H5P.externalDispatcher.on('xAPI', function (event) {	            //listenting the XAPI statements
		var obj = JSON.stringify(event.data.statement);
        request = new XMLHttpRequest();                                 //posting the statement to the controller
		request.open("POST", "vizh5p/postjson", true);
		request.setRequestHeader("Content-type", "application/json");
		request.send(obj);
});
} , false);