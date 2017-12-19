var myApp = angular.module("myApp" , ['ui.router', 'ngRoute'])

myApp.value('maps', {
    'priority'     :{ 1: 'thấp' , 2:'bình thường' , 3: 'cao' , 4: 'feedback' , 5:'khẩn cấp'},
    'ticket_status':{1: 'new' , 2:'inprogress' ,3:'resolved', 4:'feedback', 5:'closed' , 6:'cancelled' ,7:'out_of_date'},
    'rating'       :{0:'không hài lòng' , 1:'hài lòng'},
    'type'         :{0:'không', 1:'đánh giá' , 2:'thay đổi độ ưu tiên' , 3:'thay đổi deadline'},
    'ticket_read_status'    :{0:'chưa đọc' , 1:'đã đọc'}
})




myApp.value('EV_dictionary' , {
    'my_request': 'Việc tôi yêu cầu',
    'related_request': 'Công việc liên quan',
    'mission': 'Nhiệm vụ được giao',
    'team_request': 'Công việc của nhóm'
})



myApp.config(function($stateProvider, $urlRouterProvider) {
    
    $stateProvider
        .state('home', {
            url: '/home',
            templateUrl: './app/components/Home/home.html',
        })
    $stateProvider
        .state('dashBoard' , {
            url: '/dash_board/:name/:condition',
            templateUrl:'app/components/dashboard/dashboard.html',
            controller: 'dashBoardController',
        })


    $stateProvider
        .state('ticketDetail' , {
            url: '/ticket/:id',
            templateUrl:'app/components/ticketDetail/ticketDetail.html',
            controller: 'ticketDetailController'
        })

    $urlRouterProvider.otherwise('/dash_board/my_request/all')
})


myApp.run(['$rootScope' ,'$http',  function( $rootScope , $http){
    $rootScope.user = new User()
    $rootScope.user.id = 1
    $rootScope.user.team_id = 1
    $rootScope.userName = "trungthanhnguyen"
    // $http.get('/get_user').success( function(response){
    //         if( response.user && response.user.id )
    //             $rootScope.user = new User(user)
    //         else
    //             alert('dữ liệu sai')
    //         })
    //     .error( function( data){
    //         alert("dữ liệu sai")
    //     })
}])



myApp.directive('uploadFiles', function () {  
    return {  
        scope: true,        //create a new scope  
        link: function (scope, el, attrs) {  
            el.bind('change', function (event) {  
                var files = event.target.files  
               for (var i = 0 ;i < files.length; i++) {  
                    scope.$emit("seletedFile", { file: files[i] })  
                }  
            })  
        }  
    }  
}) 



    

















myApp.component('sideBar',{
    templateUrl: './app/components/SideBar/sideBar.html',
    controller: 'sideBarController',
    bindings:{
        name: '=',
    },
})
myApp.component('newRequest', {
    templateUrl: './app/components/newRequest/newRequest.html',
    controller: 'newRequestController',
    bindings:{
        ticket: '=',
    }
})
myApp.component('myFooter',{
    templateUrl: './app/common/footer.html',
})











myApp.filter('underline', function(){
    return function(input){
        return input.replace(" " , "_")
    }
})
myApp.filter('toNomal' , function(){
    return function(input){
        input = input.charAt(0).toUpperCase() + input.slice(1)
        return input.replace("_" ," ")
    }
})













myApp.filter('toPriority', ['mapService' , function( mapService){
    return function(input){
        result = mapService.map(input , 'priority')
        return result
    }
}])
myApp.filter('toTicketStatus', ['mapService' , function( mapService){
    return function(input){
        return mapService.map(input , 'ticket_status')
    }
}])
myApp.filter('toRating', ['mapService' , function( mapService){
    return function(input){
        return mapService.map(input , 'rating')
    }
}])
myApp.filter('toTypeComment', ['mapService' , function( mapService){
    return function(input){
        return mapService.map(input , 'type')
    }
}])
myApp.filter('toReadStatus', ['mapService' , function( mapService){
    return function(input){
        return mapService.map(input , 'ticket_read_status')
    }
}])
myApp.filter('VietNamTrans' , ['EV_dictionary' , function(EV_dictionary){
    return function(input){
        return EV_dictionary[input]
    }
}])











myApp.service('mapService' , [ 'maps' , function(maps){
    this.map = function( input,map_name){
         return maps[map_name][input]
    }
}])


myApp.service('conditionFilterService', function(){
    this.filterCondition = function( condition){
        result = {}
        for (var pro in condition){
            if (condition[pro] )
                 result[pro] = condition[pro]
        }
        return result
    }
})

myApp.service('ticketService', ['conditionFilterService', '$http', function(conditionFilterService ,$http){
    this.getTicket = function(id){
        //fake data
        ticket = new Ticket()
        ticket.id = id
        return ticket
    }
    this.getTickets = function( condition){
        condition.status = parseInt(condition.status)
        condition.priority = parseInt(condition.priority)
        condition = conditionFilterService.filterCondition(condition)
        console.log(condition)
    }
    this.deleteTickets = function(id){
    }
    this.newTicket = function(data){
        if( angular.isArray(data)){
            Tickets = []
            for( var obj in data){
                Tickets.push( new Ticket(obj))
            }
            return Tickets
        }
        else{
            return new Ticket(data)
        }
    }

    this.saveTicket = function(ticket){
        data = {}
        for (var pro in ticket)
            if( ticket[pro])
                data[pro] = ticket[pro]
        $http({
            method: 'POST',
            url: "/Api/PostStuff",
            data: data
        }).
        success(function (data, status, headers, config) {
            alert("success!");
        }).
        error(function (data, status, headers, config) {
            alert("failed!");
        });
        console.log(data)
    }
    this.countTicket = function(condition){

    }
    this.checkTicket = function( ticket){
        if( ticket.status == -1 
            || ticket.priority == -1 || ticket.rating == -1 || ticket.team_id == -1 
        ){
            return false
        }
        return true
    }




    this.fakeData = function(_id){
        data = {id: _id,
                subject:"this is subject "+ _id.toString(),
                content:"this is content",
                creat_by:1
        }
        return new Ticket(data)
    }
    this.fakeDataList = function(dataNumber){
        dataList = []
        for( i = 0 ;i < dataNumber; i++ )
            dataList.push( this.fakeData(i))
        return dataList
    }
}])






myApp.controller('sideBarController',['$scope', function($scope){
    $scope.name = ""
    $scope.show = false
    $scope.changeShow = function(){
        $scope.show = ! $scope.show
    }
}])

myApp.controller('dashBoardController'  , ['$scope','$stateParams','ticketService' , '$rootScope', 'maps', function($scope,$stateParams, ticketService , $rootScope,maps){
    $scope.name                     = $stateParams.name
    $scope.status                   = $stateParams.condition
    $scope.condition                = new Condition()
    $scope.tickets = ticketService.fakeDataList(10)
    $scope.paginate_params          = new PaginatePrams()
    
    for( i = 0 ; i < $scope.tickets.length ;i++){
        $scope.tickets[i].index = i+1
    }


    $scope.getTickets = function( condition = $scope.condition){
        if( $scope.status != 'all' && maps.ticket_status[condition.status] != $scope.status )
            alert("không thể tìm kiếm trạng thái khác")
        else    
            $scope.tickets = ticketService.getTickets( condition)
    }
   
    $scope.reload = function(){
        $scope.resetCondition()
        $scope.initCondition()
        $scope.tickets = ticketService.getTickets( condition)
    }

    $scope.initCondition = function(){
        if( $scope.name == "my_request"){
            $scope.condition.user_id = $rootScope.user.id
        }
        if( $scope.name == "related_request"){
            $scope.condition.related_user_id = $rootScope.user.id
        }
        if( $scope.name == "mission"){
            $scope.condition.employee_id = $rootScope.user.id
        }
        if( $scope.name == "team_request"){
            $scope.condition.team_id = $rootScope.user.team_id
        }
        if( $scope.status == 'inprogress')
             $scope.condition.status = 2 
        if( $scope.status == 'resolved')
             $scope.condition.status = 3
        if( $scope.status == 'out_of_date')
             $scope.condition.status = 7
        
    }

    $scope.resetCondition = function(){
        $scope.condition = new Condition()
        $scope.initCondition()
    }

    $scope.getStatus = function(){
        return [1,2,3,4,5,6,7]
    }
    $scope.getPriority = function(){
        return [1,2,3,4,5]
    }
    $scope.showStatusSelect = function(){
        return $scope.status == 'all'
    }


    $scope.initCondition()


}])



myApp.controller('ticketDetailController' , ['$scope' , '$stateParams','ticketService' , function( $scope , $stateParams, ticketService){
    $scope.id = $stateParams.id
    $scope.ticket = ticketService.getTicket($scope.id)


}])
    


myApp.controller('newRequestController' , ['$scope' , 'ticketService',function($scope , ticketService){
    $scope.ticket = new Ticket()
    $scope.ticket.deadline = new Date()
    $scope.$on("seletedFile", function (event, args) {  
        $scope.$apply(function () {  
            $scope.ticket.files = args.file
        })  
    })

    $scope.save = function(){
        console.log($scope.ticket)
        if (ticketService.checkTicket( $scope.ticket )){
            ticketService.saveTicket( $scope.ticket)
        }
        else{
            alert('fail to create new ticket')
        }
    }
}])
