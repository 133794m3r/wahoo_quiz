/**
 * Wahoo Quiz Project
 * Macarthur Inbody <admin-contact@transcendental.us>
 * Licensed under AGPLv3 Or Later (2021)
 */
"use strict";
/**
 *
 * The function below is a wrapper around fetch to make it work with POST requests when using Django.
 *
 * @param {string} route The URL that we're submitting to.
 * @param {object} content The parameters/variables that are to be included in the POST request.
 * @param {function(...[*]=)} callback A function to be called on the end of the fetch request.
 */
function submit(route,content,callback=null){
	//not used right now b/c it's an AJAX request by using this header and CORS CSRF won't work.
	// const csrftoken = cookie_value('csrftoken');
	fetch(route,{
		method:"POST",
		headers:{
			'Content-Type':'application/json',
			'X-Requested-With':'XMLHttpRequest'
		},
		referrerPolicy: "same-origin",
		credentials:"same-origin",
		body:JSON.stringify(content)
		})
		.then(response=>response.json())
		.then(result=>{
			if(callback){
				callback(result)
			}
		})
		.catch(error=>{
			console.log("Error:",error);
		});
}


/**
 * This is just a wrapper around fetch for my get reqeusts to give me the json result.
 *
 * @param {string} route The URL we're submitting to.
 * @param {function(...[*]=)} callback The callback function to call upon the end of the fetch request.
 */
function get(route,callback){
	fetch(route,{
		referrerPolicy: "same-origin",
	})
		.then(response =>response.json())
		.then(result=>{
			callback(result)
		})
		.catch(error=>{
			console.log("Error:",error);
		});
}


/**
 *
 * Gets a value from a cookie based upon the string name after exploding the cookie.
 * @param {string} name The name of the value we need to get.
 * @returns {null|string} The value of that property. Returns null if it isn't set.
 */
function cookie_value(name){
	let value = null;
	if(document.cookie && document.cookie !== ''){
		const cookies = document.cookie.split(';');
		let cookie = '';
		for(let i=0;i<cookies.length;i++){
			cookie = cookies[i].trim();
			if(cookie.startsWith(`${name}=`)){
				value = decodeURIComponent(cookie.substring(name.length+1));
				break;
			}
		}
	}
	return value;
}


//TODO: Use the Bootstrap modal's event when it's hidden return all fields back to default for _all_ models when we're just overwriting stuff anyways this way we don't have to anything if it's the default state anyways.

/**
 * Function to copy to the clipboard.
 * @param input_text The text to copy to the clipboard.
 */
function copyToClipBoard(input_text){

	if(!navigator.clipboard){
		const textArea = document.createElement("textarea");
		textArea.value = input_text;

		// Avoid scrolling to bottom
		textArea.style.top = "0";
		textArea.style.left = "0";
		textArea.style.position = "fixed";

		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			let successful = document.execCommand('copy');
			let msg = successful ? 'successful' : 'unsuccessful';
		}
		catch (err) {
			console.error(err);
		}
		document.body.removeChild(textArea);
	}
	else{
		navigator.clipboard.writeText(input_text).then(function () {
			console.log('worked');
		}, function () {
			console.log('did not work');
		});
	}
}