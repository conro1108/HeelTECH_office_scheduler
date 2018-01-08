var Reservation = function (reservation_json) {
    this.resID = reservation_json.resID;
    this.userID = reservation_json.userID;
    this.officeID = reservation_json.officeID;
    this.starttime = reservation_json.starttime;
    this.duration = reservation_json.duration;
    this.description = reservation_json.description;
};

Reservation.prototype.makeReservationDiv = function(){
    var reservation_div = $("<div class='resDiv'></div>");

    reservation_div.append("Reserved at " + this.starttime +
            " for " + this.duration + " minutes for " + this.description);

    reservation_div.data('resID', this.resID);
    reservation_div.data('userID', this.userID);
    reservation_div.data('officeID', this.officeID);

    return reservation_div;
};
