$(document).ready(function(event){

    let tableHours = {
        '1' : '10:00',
        '2' : '11:00',
        '3' : '12:00',
        '4' : '13:00',
        '5' : '14:00',
        '6' : '15:00',
        '7' : '16:00',
        '8' : '17:00',
        };

    // let tableHours = fetchAppointmentHours();
    // fetchAppointmentHours();
    let pickedHours = document.getElementById('pickedHours');

    // Exit early if pickedHours element doesn't exist (not on appointment page)
    if (!pickedHours) {
        return;
    }

    let pickedHoursValue = pickedHours.value;

    // let key2 = getKeyByValue1(tableHours, pickedHoursValue);

    // console.log('tableHours');
    // console.log(key2);


    if(pickedHoursValue !== ''){
        let key = getKeyByValue(tableHours, pickedHoursValue);
        let elId = 'hour_select_'+key;
        let a = document.getElementById(elId);
        a.style.setProperty('background-color', '#f0006c');
    }

});


function fetchAppointmentHours() {
    fetch('HandleRequest.php?function=getAppointmentHours') // The endpoint for AJAX
        .then(response => response.json()) // Parse the JSON response
        .then(data => {
            if (data.appointments) {
                // console.log("Appointments:", data.appointments);
                // Example: Display the appointments in the console
                // const appointmentsList = document.getElementById('appointments');
                // appointmentsList.innerHTML = ''; // Clear existing list
                // for (const [id, time] of Object.entries(data.appointments)) {
                //     const listItem = document.createElement('li');
                //     listItem.textContent = `ID: ${id}, Time: ${time}`;
                //     appointmentsList.appendChild(listItem);
                // }
                tableHours = data.appointments;
                // return data.appointments;
                // return da
            } else if (data.error) {
                console.error("Error:", data.error);
            }
        })
        .catch(error => console.error('Error fetching appointments:', error));
}

function getKeyByValue(object, value) {
    console.log(object);
    return Object.keys(object).find(key =>
        object[key] === value);
}

function pickHour(data) {
    let hour = document.getElementById("pickedHours");
    // let a = JSON.parse(data);
    // hour.value = a[1];
    hour.value = data;
}