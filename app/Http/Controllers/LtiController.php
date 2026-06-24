<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LonghornOpen\LaravelCelticLTI\LtiTool;

class LtiController extends Controller
{
    // LMSes require that you provide a JSON Web Token (JWT).  The easiest way to provide that is to
    // have a URL that returns the JWT.  You can then use that URL in your LMS configuration, as a
    // JSON Web Key Set (JWKS) URL.
    public function getJWKS()
    {
        $tool = LtiTool::getLtiTool();
        return $tool->getJWKS();
    }

    // This handles the LTI launch; it'll be called by the LMS when the user clicks on the link to
    // your tool.  Several database IDs will be available from the LtiTool object once handleRequest()
    // is called.  You should store these in a session or in your app's database, so that you can
    // use them later.
    public function ltiMessage(Request $request)
    {
        $tool = LtiTool::getLtiTool();
        $tool->handleRequest();

        /*
        At this point:
          $tool->platform describes the platform (LMS)
          $tool->context describes the context (course)
          $tool->resourceLink describes the resourceLink (tool placement in course)
          $tool->userResult describes the user, including their role in the course.

        Each of these has a getRecordId() function which returns a database primary key.
        Store these keys in a session or in your app's database for later lookup.
        If your app has database tables corresponding to courses, users, etc you can store this primary key in that table.
        */
        $request->session()->put('context_id', $tool->context?->getRecordId());
        $request->session()->put('platform_id', $tool->platform?->getRecordId());
        $request->session()->put('user_result_id', $tool->userResult?->getRecordId());
        $request->session()->put('resource_link_id', $tool->resourceLink?->getRecordId());

        // Finally, redirect the user to your app's home page, or whatever page the user should see when they
        // launch your tool.
        return redirect('/my_app_home');
    }

    // The app's home page, which the user will see once they launch the tool.  Here, we just provide a set
    // of links to pages demoing the functionality.
    public function myAppHome()
    {
//        dd("Successful launch!", $tool);
        return "<a href='/testRoster'>Test Roster</a><br>
        <a href='/testLineItem'>Test Viewing Line Items</a><br>
        <a href='/testLineItemSet'>Test Setting Line Items</a><br>
        <a href='/testLineItemUpdateScore'>Test Updating Scores on Line Items</a><br>
        <a href='/test1'>Test 1</a><br>
        <a href='/test2'>Test 2</a><br>
        <a href='/test3'>Test 3</a><br>
        <a href='/test4'>Test 4</a>";
    }

    // If you've stored the Context ID during the launch, you can use it to look up stored information about
    // the course, such as its name.  You can also call LTI services on the context, such as getting the
    // course's roster.
    //
    // LTI services such as the Membership service (called by `getMemberships()` here) are defined by a standard
    // set of URIs.  In order to call this service, you'll need to have the appropriate URI listed in
    // config/lti.php under the 'required_scopes' key, and you'll probably need to enable the service in your
    // LMS.  (How you do this is LMS-specific.)
    //
    // Objects such as $context here are the raw objects from the CeLTIc LTI library (https://github.com/celtic-project/LTI-PHP).
    // See their documentation for more information about how to use them.
    public function testRoster(Request $request)
    {
        $tool = LtiTool::getLtiTool();
        $context = $tool->getContextById(session('context_id'));
        dd($context, $context->getMemberships());
    }

    // Line Items are a way for your tool to communicate students' scores/grades and other outcomes back to the
    // LMS.  Many LMSes show Line Items as one or more gradebook columns, which are associated with the tool.
    // `getLineItems()` here calls the LTI Assignment and Grade Services (AGS) to get the list of this tool's Line Items.
    //
    // As with `testRoster()` above, you'll have to know the find the URI identifying the service you want to call,
    // and list it in config/lti.php under the 'required_scopes' key, as well as configuring your LMS to enable the
    // service.
    public function testLineItem()
    {
        $tool = LtiTool::getLtiTool();
        $context = $tool->getContextById(session('context_id'));
        $platform = $tool->getPlatformById(session('platform_id'));
        dd("Current line items", $context->getLineItems());
    }

    // Similar to `testLineItem()` above, but here we're creating a line item instead of reading them.
    public function testLineItemSet()
    {
        $tool = LtiTool::getLtiTool();
        $context = $tool->getContextById(session('context_id'));
        $platform = $tool->getPlatformById(session('platform_id'));

        // create a new line item named 'Test LI' with a max-score of 100
        $lineitem = new \ceLTIc\LTI\LineItem($platform, 'Test LI', 100);
        $lineitem->label = 'LI Label';
        $lineitem->resourceId = 'LI resource ID';
        $lineitem->tag = 'LI tag';
        $lineitem->endpoint = 'LI endpoint';
        dd("Created line item?", $context->createLineItem($lineitem));
    }

    // Similar to `testLineItemSet()` above, but here we're reading and writing outcomes (student grades)
    // to a line item which has been previously created.
    public function testLineItemUpdateScore()
    {
        $tool = LtiTool::getLtiTool();
        $context = $tool->getContextById(session('context_id'));
        $platform = $tool->getPlatformById(session('platform_id'));
        $user_result = $tool->getUserResultById(session('user_result_id'));

        $line_item = $context->getLineItems()[0];
        // need to know the current grade for some reason?
        //$outcome = $line_item->readOutcome($user_result);

        $outcome = new \ceLTIc\LTI\Outcome(75, 100);
        $outcome->comment = 'Very good!';
        $ok = $line_item->submitOutcome($outcome, $user_result);

        dd("Updated score?", $ok);
    }

    // These functions left blank for you to fill in with your own tests.
    public function test1()
    {

    }

    public function test2()
    {
    }

    public function test3()
    {
    }

    public function test4()
    {
    }
}
