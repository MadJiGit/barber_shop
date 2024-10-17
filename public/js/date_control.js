$(document).ready(function(){

    let left_arrow = document.getElementById("arrow_left");
    let left_arrow_button = document.getElementById("take_out_a_day_arrow");
    let right_arrow = document.getElementById("arrow_right");
    let right_arrow_button = document.getElementById("add_a_day_arrow");
    let calendarElement = document.getElementById('calendar');

    let min_date = calendarElement.min;
    let max_date = calendarElement.max;

    checkDateAndSetButtons();

    left_arrow_button.addEventListener("click", function (event){
        event.preventDefault();
        let dateFromForm = new Date(calendarElement.value);
        let newDateString = new Date(dateFromForm).setDate(dateFromForm.getDate() - 1);
        let fullDate = concatFullDate(newDateString)
        changeDate(calendarElement, fullDate);
        checkDateAndSetButtons();
    })
    right_arrow_button.addEventListener("click", function (event){
        event.preventDefault();
        let dateFromForm = new Date(calendarElement.value);
        let newDateString = new Date(dateFromForm).setDate(dateFromForm.getDate() + 1);
        let fullDate = concatFullDate(newDateString)
        changeDate(calendarElement, fullDate);
        checkDateAndSetButtons();
    })

    function checkDateAndSetButtons(){
        let dateFromForm = new Date(calendarElement.value);
        let today = concatFullDate(new Date());
        left_arrow_button.disabled = min_date <= dateFromForm;
        right_arrow_button.disabled = max_date >= dateFromForm;
    }

    function changeDate(calendarElement, newDate) {
        let todayObject = new Date();
        // let today =  todayObject.getFullYear() + '-' + (todayObject.getMonth()+1)  + '-' + todayObject.getDate();
        let today = concatFullDate(new Date());
        if(newDate < today){
            console.log('new date cannot be less than today');
            return;
        }
        if(newDate > max_date){
            console.log('new date cannot be bigger than max day');
            return;
        }

        // set new date to calendar form
        calendarElement.value = newDate;
    }

    function concatFullDate(newDateString) {
        let newDateObject = new Date(newDateString);
        return newDateObject.getFullYear() + '-' + (newDateObject.getMonth()+1)  + '-' + newDateObject.getDate()  ;
    }

    calendar.addEventListener("change", function (event){
        checkDateAndSetButtons();
    })

});