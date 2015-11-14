var app = angular.module('Microblog', ['ngRoute']);

app.config(['$interpolateProvider', function ($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
}]);

app.controller('PostController', ['$scope', '$http', function($scope, $http) {

    $scope.form = {};

    $scope.submit = function() {
        console.log($.param($scope.form));

        $http({
            method  : 'POST',
            url     : $scope.url,
            data    : $.param($scope.form),  // pass in data as strings
            headers : { 'Content-Type': 'application/x-www-form-urlencoded' }  // set the headers so angular passing info as form data (not request payload)
        })
        .success(function(data) {
            console.log(data);

        });
    };
}]);