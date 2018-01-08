var base_url = 'https://wwwp.cs.unc.edu/Courses/comp426-f17/users/cdrowe/heelTECH/server/controller.php';

$(document).ready(function(){
    setupLogin();
});

var setupLogin = function() {
    $('#navBar').empty();
    var center_div = $('#centerDiv');
    var lower_div = $('#lowerDiv');

    center_div.empty();
    lower_div.empty();

    var login_div = $('<div id="loginDiv"></div>');

    var userInput = $("<input type='text' name='username' value='Enter Username' id='userInput' class='loginElem'>");
    var passInput = $("<input type='password' name='password' value='Enter Password' id='passInput' class='loginElem'>");

    login_div.append(userInput);
    login_div.append('<br>');
    login_div.append(passInput);

    var buttons_div = $("<div id='buttonsDiv'></div>");

    var login_button = $("<button type='button' id='loginBtn' class='loginBtn'>Log In</button>");
    var register_button = $("<button type='button' id='loginRegBtn' class='loginBtn'>Register</button>");

    buttons_div.append(login_button);
    buttons_div.append(register_button);

    center_div.append(login_div);
    lower_div.append(buttons_div);

    $('#loginBtn').on('click', function() {
        var user = userInput.val();
        var pass = passInput.val();
        login(user, pass);
    });

    $('#loginRegBtn').on('click', function () {
        loginToRegister(login_div, buttons_div);
    });
};

var loginToRegister = function (login_div, buttons_div) {
    buttons_div.empty();

    var perm_menu = $("<select name='perm' class='loginElem'>" +
                    "<option value='read'>Read</option>" +
                    "<option value='employee'>Employee</option>" +
                    "<option value='executive'>Executive</option>" +
                    "<option value='admin'>Administrator</option>" +
                    "</select>");

    var register_button = $("<button type='button' id='registerBtn' class='loginBtn'>Register</button>");

    login_div.append('<br>');
    login_div.append(perm_menu);
    buttons_div.append(register_button);

    $('#registerBtn').on('click', function(){
        var user = $('#userInput').val();
        var pass = $('#passInput').val();
        var perm = perm_menu.val();

        register(user, pass, perm);
    });
};

var login = function(username, password){
    $.ajax(base_url + '/users/' + username,
        {type: 'GET',
            dataType: 'json',
            data: {'password': password},
            success: function(user_json, status, jqXHR){
                $('#navBar').empty();
                loadApp(user_json);
            },
            error: function(jqXHR, status, error){
                alert("Username and/or password incorrect.");
            }});
};

var register = function(username, password, permission){
    $.ajax(base_url + '/users',
        {type: 'POST',
        dataType: 'json',
        data: {'username': username, 'password': password, 'perm': permission},
        success: function (user_json, status, jqXHR) {
            alert("New account created successfully!");
            setupLogin();
        },
        error: function (jqXHR, status, error) {
            alert("Something went wrong :/");
        }});
};

var loadApp = function(active_user){
    var nav_bar = $('#navBar');
    nav_bar.empty();
    var logout_button = $("<span id='logout'>Log Out!</span>");
    logout_button.on('click', function (e) {
        e.preventDefault();
        setupLogin();
    });
    nav_bar.append(logout_button);
    var user_display = $("<span id='userDisp'></span>");
    user_display.append("Logged in as: " + active_user.username);
    nav_bar.append(user_display);
    var center_div = $('#centerDiv');
    var lower_div = $('#lowerDiv');

    center_div.off('click');
    center_div.empty();
    lower_div.empty();

    $.ajax(base_url + '/offices',
        {type: 'GET',
        dataType: 'json',
        success: function (office_ids, status, jqXHR) {
            for(var i=0; i<office_ids.length; i++){
                loadOffice(office_ids[i]);
            }
        }});

    center_div.on('click', '.officeBtn', function(e){
        var office = $(e.target);

        var res_info_div = $("<div id='resInfoDiv'></div>");
        var res_button_div = $("<div id='resBtnDiv'></div>");
        var lower_div = $('#lowerDiv');
        e.preventDefault();
        lower_div.empty();

        var passID = $(this).data('officeID');
        var reserve_button = $("<button type='button' id='reserveBtn'>Reserve this office</button>");
        reserve_button.on('click', function(e){ // transition from resInfoDiv to scheduleDiv
            var office_perm = office.data('perm');
            var user_perm = active_user['perm'];

            var hasPermission = validate(user_perm, office_perm);

            if(!hasPermission){
                alert("You do not have permission to reserve this office!");
                $('#navBar').empty();
                loadApp(active_user);
            } else {
                setUpSchedule(lower_div, active_user, passID);
            }



        });

        res_button_div.append(reserve_button);

        lower_div.append(res_info_div);
        lower_div.append(res_button_div);
        $.ajax(base_url + '/reservations/offices/' + $(this).data('officeID'),
            {type: 'GET',
            dataType: 'json',
            success: function (res_ids, status, jqXHR) {
                var idx = [];
                for(var i = 0; i < res_ids.length; i++){
                    //ridiculous hack to get around setTimeout() scope challenges. Timing keeps AJAX requests ordered properly
                    var arr = res_ids;
                    idx.push(i);
                    var count = 0;
                    setTimeout(function (){
                        loadReservation(arr[idx[count]]);
                        count += 1;
                    }, i * 20);
                }
            }});
    });
};

var loadOffice = function(id){
    $.ajax(base_url + '/offices/' + id,
        {type: 'GET',
        dataType: 'json',
        success: function (office_json, status, jqXHR) {
            var ofc = new Office(office_json);
            var ofc_div = ofc.makeOfficeDiv();

            $('#centerDiv').append(ofc_div);
        }});
};

var loadReservation = function(id){
    $.ajax(base_url + '/reservations/' + id,
        {type: 'GET',
            dataType: 'json',
            success: function (rec_json, status, jqXHR) {
                var res = new Reservation(rec_json);
                $('#resInfoDiv').append(res.makeReservationDiv());
            }});
};

var validate = function(userPerm, permReq){
    if (userPerm === "admin") {
        return true;
    } else if (userPerm === 'executive' && permReq !== 'admins') {
        return true;
    } else if (userPerm === 'employee') {
        if ((permReq === 'read' || permReq === 'employee')) {
            return true;
        }
    }
    return (userPerm === 'read' && permReq === 'read');
};

var setUpSchedule = function(lower_div, active_user, office_id){
    lower_div.empty();

    var sched_form = $("<form id='schedForm'></form>");

    /*
    var officeID_div = $("<div class='schedElem'></div>");
    var officeID_input = $("<input id='officeIDInput' name='officeID' type='text'>");
    officeID_div.append("Office ID:<br>");
    officeID_div.append(officeID_input);
    */

    var time_div = $("<div class='schedElem'></div>");
    var time_input = $("<input id='timeInput' name='starttime' type='text'>");
    time_div.append("Start Time:<br>");
    time_div.append(time_input);

    var duration_div = $("<div class='schedElem'></div>");
    var duration_input = $("<input type='text' name='duration' id='durationInput' maxlength=3>");
    duration_div.append("Duration:<br>");
    duration_div.append(duration_input);

    var description_div = $("<div class='schedElem' id='descriptionDiv'></div>");
    var description_field = $("<textarea name='description' id='descriptionField'></textarea>");
    description_div.append("Quick description of activity:<br>");
    description_div.append(description_field);

    //sched_form.append(officeID_div);
    sched_form.append(time_div);
    sched_form.append(duration_div);
    sched_form.append(description_div);

    var submit_button = $("<input type='submit' value='Schedule Appointment'>");

    sched_form.append(submit_button);

    sched_form.on('submit', function(e){
       e.preventDefault();
       console.log(active_user.userID);
        $.ajax(base_url + '/reservations',
            {type: 'POST',
            dataType: 'json',
            data: $(this).serialize() + '&userID=' + encodeURIComponent(active_user.userID)
            + '&officeID=' + encodeURIComponent(office_id),
            success: function (created_json, status, jqXHR) {
                loadApp(active_user);
            },
            error: function (jqXHR, status, error) {
                alert(jqXHR.responseText);
            }});
    });
    lower_div.append(sched_form);
};