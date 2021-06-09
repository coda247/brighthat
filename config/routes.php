<?php
declare(strict_types=1);
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;




return function (App $app)
{
    $app->group("/auth",function($app)
    {
       $app->post("/login",[\App\Controllers\AuthController::class,"Login"]);
       $app->post("/register",[\App\Controllers\AuthController::class,"Register"]);
       $app->post("/social",[\App\Controllers\AuthController::class,"SocialLogin"]);
       $app->post("/verify",[\App\Controllers\AuthController::class,"Verify"]);
       $app->get("/accountTypes",[\App\Controllers\AuthController::class,"AccountTypes"]);
      
       
    });

    $app->group("/users",function($app)
    {
      $app->get("/users",[\App\Controllers\UserController::class,"GetUsers"]);
      $app->get("/",[\App\Controllers\UserController::class,"GetUserInfo"]);
      $app->post("/reset/link",[\App\Controllers\UserController::class,"resetLink"]);
      $app->post("/reset/password",[\App\Controllers\UserController::class,"resetPassword"]);

    });

    $app->group("/teacher",function($app)
    {
       $app->get("/teachers",[\App\Controllers\TeacherController::class,"GetTeachers"]);
       $app->get("/classes",[\App\Controllers\TeacherController::class,"GetTeacherClasses"]);
       $app->get("/topics",[\App\Controllers\TeacherController::class,"GetTeacherTopics"]);
       $app->get("/sessions",[\App\Controllers\TeacherController::class,"GetTeacherSessions"]);
       $app->get("/overview",[\App\Controllers\TeacherController::class,"GetTeacherOverview"]);
       $app->get("/getHour",[\App\Controllers\TeacherController::class,"GetTeacherWorkingHours"]);
       $app->get("/getOffDates",[\App\Controllers\TeacherController::class,"GetTeacherOffDates"]);
       $app->get("/schedule",[\App\Controllers\TeacherController::class,"GetTeacherSchedule"]);

       $app->post("/addTopic",[\App\Controllers\TeacherController::class,"AddNewTopic"]);
       $app->post("/addResume",[\App\Controllers\TeacherController::class,"AddResume"]);
       $app->post("/removeTopic",[\App\Controllers\TeacherController::class,"RemoveATopic"]);
       $app->post("/addHour",[\App\Controllers\TeacherController::class,"AddNewWorkingHour"]);
       $app->post("/removeHour",[\App\Controllers\TeacherController::class,"RemoveAWorkingHour"]);
       $app->post("/addOffDate",[\App\Controllers\TeacherController::class,"AddNewOffDate"]);
       $app->post("/removeOffDate",[\App\Controllers\TeacherController::class,"RemoveAnOffDate"]);
    });

    $app->group("/student",function($app)
    {
       $app->get("/students",[\App\Controllers\StudentController::class,"GetStudents"]);
       $app->get("/classes",[\App\Controllers\StudentController::class,"GetStudentClasses"]);
       $app->get("/sessions",[\App\Controllers\StudentController::class,"GetStudentSessions"]);
       $app->get("/overview",[\App\Controllers\StudentController::class,"GetStudentOverview"]);
       $app->get("/schedule",[\App\Controllers\StudentController::class,"GetStudentSchedule"]);

    });


    $app->group("/parent",function($app)
    {
       $app->get("/parents",[\App\Controllers\ParentController::class,"GetParents"]);
       $app->get("/children",[\App\Controllers\ParentController::class,"GetParentChildren"]);
       $app->get("/childOverview",[\App\Controllers\ParentController::class,"GetChildOverView"]);
       $app->get("/overview",[\App\Controllers\ParentController::class,"GetParentOverview"]);

       $app->post("/add",[\App\Controllers\ParentController::class,"RegisterChild"]);

    });


   $app->group("/sub",function($app)
    {
       $app->get("/subscriptions",[\App\Controllers\ClassSubscriptionController::class,"GetSubscriptions"]);
       $app->get("/sessions",[\App\Controllers\ClassSubscriptionController::class,"GetClassSessions"]);
       $app->get("/info",[\App\Controllers\ClassSubscriptionController::class,"GetSubsriptionInfo"]);
       
       $app->post("/add",[\App\Controllers\ClassSubscriptionController::class,"AddNewSubscription"]);

    });

    $app->group("/subject",function($app)
    {
       $app->get("/subjects",[\App\Controllers\SubjectsController::class,"GetSubjects"]);
       $app->get("/info",[\App\Controllers\SubjectsController::class,"GetSubjectInfo"]);

       $app->post("/create",[\App\Controllers\SubjectsController::class,"NewSubject"]);
       $app->post("/update",[\App\Controllers\SubjectsController::class,"EditSubject"]);
       $app->post("/delete",[\App\Controllers\SubjectsController::class,"DeleteASubject"]);
    });

    $app->group("/class",function($app)
    {
       $app->get("/classes",[\App\Controllers\ClassGradeController::class,"GetClassGrades"]);
       $app->get("/info",[\App\Controllers\ClassGradeController::class,"GetClassInfo"]);
       
       $app->post("/create",[\App\Controllers\ClassGradeController::class,"NewClassGrade"]);
       $app->post("/update",[\App\Controllers\ClassGradeController::class,"EditClass"]);
       $app->post("/delete",[\App\Controllers\ClassGradeController::class,"DeleteClassGrade"]);
    });

    
    $app->group("/category",function($app)
    {
       $app->get("/categories",[\App\Controllers\ClassCategoryController::class,"GetClassCategories"]);
       $app->get("/info",[\App\Controllers\ClassCategoryController::class,"GetCategoryInfo"]);
       
       $app->post("/create",[\App\Controllers\ClassCategoryController::class,"NewClassCategory"]);
       $app->post("/update",[\App\Controllers\ClassCategoryController::class,"EditCategory"]);
       $app->post("/delete",[\App\Controllers\ClassCategoryController::class,"DeleteClassCategory"]);
    });


   $app->group("/outline",function($app)
    {
       $app->get("/outlines",[\App\Controllers\TopicOutlineController::class,"GetTopicOutlines"]);
       $app->get("/info",[\App\Controllers\TopicOutlineController::class,"GetOutlineInfo"]);
       
       $app->post("/create",[\App\Controllers\TopicOutlineController::class,"NewOutline"]);
       $app->post("/update",[\App\Controllers\TopicOutlineController::class,"EditOutline"]);
       $app->post("/delete",[\App\Controllers\TopicOutlineController::class,"RemoveOutline"]);
    });


    $app->group("/topic",function($app)
    {
       $app->get("/topics",[\App\Controllers\TopicController::class,"GetTopics"]);
       $app->get("/info",[\App\Controllers\TopicController::class,"GetTopicInfo"]);

       $app->post("/create",[\App\Controllers\TopicController::class,"NewTopic"]);
       $app->post("/update",[\App\Controllers\TopicController::class,"EditTopic"]);
       $app->post("/delete",[\App\Controllers\TopicController::class,"DeleteATopic"]);
       $app->post("/changeCover",[\App\Controllers\TopicController::class,"ChangeCover"]);
    });

    $app->group("/rating",function($app)
    {
       $app->get("/classRating",[\App\Controllers\RatingController::class,"GetClassRating"]);
       $app->get("/teacherRating",[\App\Controllers\RatingController::class,"GetTeacherRating"]);

       $app->post("/saveRating",[\App\Controllers\RatingController::class,"NewRating"]);
    });

    $app->group("/review",function($app)
    {
       $app->get("/classReview",[\App\Controllers\ReviewController::class,"GetClassReviews"]);
       $app->get("/teacherReview",[\App\Controllers\ReviewController::class,"GetTeacherReviews"]);
       $app->get("/generalReview",[\App\Controllers\ReviewController::class,"GetGeneralReviews"]);

       $app->post("/saveReview",[\App\Controllers\ReviewController::class,"NewReview"]);
    });


    $app->group("/schedule",function($app)
    {
       $app->get("/teacherSchedule",[\App\Controllers\ScheduleController::class,"GetTeacherSchedule"]);
       $app->get("/classSchedule",[\App\Controllers\ScheduleController::class,"GetClassSchedule"]);
       $app->get("/sedondaryTeachers",[\App\Controllers\ScheduleController::class,"GetSecondaryTeachers"]);
       $app->get("/checkSecondaryTeacher",[\App\Controllers\ScheduleController::class,"SecondaryTeacherCheck"]);
       $app->get("/scheduleInfo",[\App\Controllers\ScheduleController::class,"GetScheduleInfo"]);
       
       
       $app->post("/saveSchedule",[\App\Controllers\ScheduleController::class,"SaveNewSchedule"]);
       $app->post("/deleteSchedule",[\App\Controllers\ScheduleController::class,"DeleteSchedule"]);
    });

    $app->group("/mock",function($app)
    {
       $app->get("/exams",[\App\Controllers\MockExamController::class,"GetExams"]);
       $app->get("/examQuestions",[\App\Controllers\MockExamController::class,"GetExamQuestions"]);
       $app->get("/examAnswers",[\App\Controllers\MockExamController::class,"GetExamAnswers"]);
       $app->get("/nextQuestion",[\App\Controllers\MockExamController::class,"GetNextQuestion"]);
       $app->get("/studentExamScore",[\App\Controllers\MockExamController::class,"GetStudentScore"]);
       $app->get("/examSessionInfo",[\App\Controllers\MockExamController::class,"GetExamSessionInfo"]);
       
       
       $app->post("/createExam",[\App\Controllers\MockExamController::class,"CreateExam"]);
       $app->post("/deleteExam",[\App\Controllers\MockExamController::class,"DeleteExam"]);
       $app->post("/addAnswer",[\App\Controllers\MockExamController::class,"AddAnswer"]);
       $app->post("/addQuestion",[\App\Controllers\MockExamController::class,"AddQuestion"]);
       $app->post("/editQuestion",[\App\Controllers\MockExamController::class,"EditQuestion"]);
       $app->post("/editExam",[\App\Controllers\MockExamController::class,"EditExam"]);
       $app->post("/deleteQuestion",[\App\Controllers\MockExamController::class,"DeleteQuestion"]);
       $app->post("/startExamSession",[\App\Controllers\MockExamController::class,"CreateExamSession"]);
       $app->post("/endExamSession",[\App\Controllers\MockExamController::class,"EndExamSesseion"]);
    });

    $app->group("/exam",function($app)
    {
       $app->get("/classExam",[\App\Controllers\ExamController::class,"GetExams"]);
       $app->get("/examQuestions",[\App\Controllers\ExamController::class,"GetExamQuestions"]);
       $app->get("/examAnswers",[\App\Controllers\ExamController::class,"GetExamAnswers"]);
       $app->get("/nextQuestion",[\App\Controllers\ExamController::class,"GetNextQuestion"]);
       $app->get("/studentExamScore",[\App\Controllers\ExamController::class,"GetStudentScore"]);
       $app->get("/examSessionInfo",[\App\Controllers\ExamController::class,"GetExamSessionInfo"]);
       
       
       $app->post("/createExam",[\App\Controllers\ExamController::class,"CreateExam"]);
       $app->post("/deleteExam",[\App\Controllers\ExamController::class,"DeleteExam"]);
       $app->post("/addAnswer",[\App\Controllers\ExamController::class,"AddAnswer"]);
       $app->post("/addQuestion",[\App\Controllers\ExamController::class,"AddQuestion"]);
       $app->post("/editQuestion",[\App\Controllers\ExamController::class,"EditQuestion"]);
       $app->post("/editExam",[\App\Controllers\ExamController::class,"EditExam"]);
       $app->post("/deleteQuestion",[\App\Controllers\ExamController::class,"DeleteQuestion"]);
       $app->post("/startExamSession",[\App\Controllers\ExamController::class,"CreateExamSession"]);
       $app->post("/endExamSession",[\App\Controllers\ExamController::class,"EndExamSesseion"]);
    });

    $app->group("/teacherExam",function($app)
    {
       $app->get("/classExam",[\App\Controllers\TeacherExamController::class,"GetExams"]);
       $app->get("/examQuestions",[\App\Controllers\TeacherExamController::class,"GetExamQuestions"]);
       $app->get("/examAnswers",[\App\Controllers\TeacherExamController::class,"GetExamAnswers"]);
       $app->get("/nextQuestion",[\App\Controllers\TeacherExamController::class,"GetNextQuestion"]);
       $app->get("/teacherExamScore",[\App\Controllers\TeacherExamController::class,"GetTeacherScore"]);
       $app->get("/examSessionInfo",[\App\Controllers\TeacherExamController::class,"GetExamSessionInfo"]);
       
       
       $app->post("/createExam",[\App\Controllers\TeacherExamController::class,"CreateExam"]);
       $app->post("/deleteExam",[\App\Controllers\TeacherExamController::class,"DeleteExam"]);
       $app->post("/addAnswer",[\App\Controllers\TeacherExamController::class,"AddAnswer"]);
       $app->post("/addQuestion",[\App\Controllers\TeacherExamController::class,"AddQuestion"]);
       $app->post("/editQuestion",[\App\Controllers\TeacherExamController::class,"EditQuestion"]);
       $app->post("/editExam",[\App\Controllers\TeacherExamController::class,"EditExam"]);
       $app->post("/deleteQuestion",[\App\Controllers\TeacherExamController::class,"DeleteQuestion"]);
       $app->post("/startExamSession",[\App\Controllers\TeacherExamController::class,"CreateExamSession"]);
       $app->post("/endExamSession",[\App\Controllers\TeacherExamController::class,"EndExamSesseion"]);
    });


    $app->group("/pack",function($app)
    {
       $app->get("/creditPacks",[\App\Controllers\CreditPackController::class,"GetCreditPacks"]);
       $app->get("/checkUserSub",[\App\Controllers\CreditPackController::class,"CheckUserSub"]);
       $app->get("/packInfo",[\App\Controllers\CreditPackController::class,"GetPackInfo"]);
       $app->get("/checkDueSub",[\App\Controllers\CreditPackController::class,"CheckDueSubscriptions"]);
        
       $app->post("/createPack",[\App\Controllers\CreditPackController::class,"CreateCreditPack"]);
       $app->post("/editPack",[\App\Controllers\CreditPackController::class,"EditCreditPack"]);
       $app->post("/subscribe",[\App\Controllers\CreditPackController::class,"NewSubscription"]);
       $app->post("/delete",[\App\Controllers\CreditPackController::class,"DeleteCreditPack"]);
       $app->post("/transfer",[\App\Controllers\CreditPackController::class,"TransferUnusedCredit"]);
    });


    $app->group("/priv",function($app)
    {
       $app->get("/privileges",[\App\Controllers\PriviledgeController::class,"GetPriviledges"]);
       $app->get("/userPrivileges",[\App\Controllers\PriviledgeController::class,"GetUserPriviledges"]);
       $app->get("/info",[\App\Controllers\PriviledgeController::class,"PrivilegeInfo"]);
        
       $app->post("/create",[\App\Controllers\PriviledgeController::class,"CreateSubAdmin"]);
       $app->post("/assign",[\App\Controllers\PriviledgeController::class,"AssignUserPrivilege"]);
       $app->post("/check",[\App\Controllers\PriviledgeController::class,"CheckUserPrivilege"]);
       $app->post("/unassign",[\App\Controllers\PriviledgeController::class,"UnAssignPrivilege"]);
       $app->post("/alter",[\App\Controllers\PriviledgeController::class,"SubAdminAction"]);
    });
    
};