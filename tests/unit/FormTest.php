<?php

use Illuminate\Routing\Redirector;
use Faker\Factory;

class TestForm extends \Coyote\Services\FormBuilder\Form
{
    public function buildForm()
    {
        // TODO: Implement buildForm() method.
    }
}

class SampleForm extends \Coyote\Services\FormBuilder\Form
{
    public function buildForm()
    {
        $this
            ->add('name', 'text')
            ->add('email', 'text')
            ->add('group_id', 'select', [
                'choices' => [
                    1 => 'Admin',
                    2 => 'Moderator'
                ]
            ])
            ->add('bio', 'textarea');
    }
}

class PollForm extends \Coyote\Services\FormBuilder\Form
{
    public function buildForm()
    {
        $this
            ->add('title', 'text')
            ->add('items', 'text')
            ->add('length', 'text');
    }
}

class FormTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testCreateFormAndSetAttributes()
    {
        $form = $this->createForm(TestForm::class, null, ['url' => 'http://foo.com']);
        $this->assertInstanceOf(\Illuminate\Http\Request::class, $form->getRequest());

        $this->assertEquals('http://foo.com', $form->getUrl());

        $form->setUrl('http://bar.com');
        $this->assertEquals('http://bar.com', $form->getUrl());

        $form->setAttr(['id' => 'submit-form']);
        $this->assertArrayHasKey('id', $form->getAttr());
        $this->assertEquals('http://bar.com', $form->getUrl());

        $form->setAttr(['id' => 'submit-form', 'action' => 'http://kung-fu.com']);
        $this->assertEquals('http://kung-fu.com', $form->getUrl());

        $this->assertEquals('POST', $form->getMethod());
        $form->setMethod('GET');
        $this->assertEquals('GET', $form->getMethod());

        $form->setAttr(['id' => 'foo-form']);
        $this->assertEquals('GET', $form->getMethod());
    }

    // tests
    public function testBuildFormWithDefaultValues()
    {
        $form = $this->createForm(TestForm::class);
        $this->assertInstanceOf(\Illuminate\Http\Request::class, $form->getRequest());

        $tags = collect([
            ['id' => 1, 'name' => 'c++'],
            ['id' => 2, 'name' => 'c#']
        ]);

        $form->add('name', 'text', ['value' => 'Admin']);
        $form->add('genre', 'select', [
            'choices' => [
                'male' => 'Male', 'female' => 'Female'
            ],
            'empty_value' => '-- choose --',
            'value' => 'female'
        ]);
        $form->add('confirm', 'checkbox', [
            'value' => 1
        ]);
        $form->add('tags', 'collection', [
            'property' => 'name',
            'value' => $tags,
            'child_attr' => [
                'type' => 'text'
            ]
        ]);
        $form->add('experience', 'text', [
            'value' => 0
        ]);

        $form->buildForm();

        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Text::class, $form->get('name'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Text::class, $form->getField('name'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Text::class, $form->name);

        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Select::class, $form->get('genre'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Checkbox::class, $form->get('confirm'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Collection::class, $form->get('tags'));

        $this->assertValue('name', 'Admin', $form);
        $this->assertValue('genre', 'female', $form);

        $form->genre->setValue(0);
        $this->assertValue('genre', 0, $form);
        $this->assertEquals('-- choose --', $form->genre->getEmptyValue());

        $this->assertValue('confirm', 1, $form);
        $this->assertTrue($form->confirm->isChecked());

        $form->get('confirm')->setValue(0);
        $this->assertValue('confirm', 0, $form);

        $form->get('confirm')->setChecked(true);
        $this->assertValue('confirm', 1, $form);

        $form->get('confirm')->setCheckedValue('human')->setValue('human');
        $this->assertTrue($form->get('confirm')->isChecked());

        $form->get('confirm')->setValue(false);
        $this->assertFalse($form->get('confirm')->isChecked());

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $form->get('tags')->getValue());
        $this->assertTrue(is_array($form->tags->getChildrenValues()));
        $this->assertTrue(is_array($form->tags->getChildren()));

        $this->assertEquals('c++', $form->tags->getChildren()[0]->getValue());
        $this->assertEquals('c#', $form->tags->getChildren()[1]->getValue());

        $tags = collect([
            ['id' => 1, 'name' => 'pascal'],
            ['id' => 2, 'name' => 'coyote']
        ]);

        $form->get('tags')->setValue($tags);

        $this->assertEquals('pascal', $form->tags->getChildren()[0]->getValue());
        $this->assertEquals('coyote', $form->tags->getChildren()[1]->getValue());

        $this->assertTrue(0 === $form->get('experience')->getValue());
    }

    public function testBuildFormWithCollection()
    {
        $form = $this->createForm(TestForm::class);
        $tags = collect([
            ['id' => 1, 'name' => 'c++'],
            ['id' => 2, 'name' => 'c#']
        ]);

        $form->add('tags_v1', 'collection', [
            'property' => 'name',
            'value' => $tags,
            'child_attr' => [
                'type' => 'text'
            ]
        ]);
        $form->add('tags_v2', 'collection', [
            'property' => 'name',
            'child_attr' => [
                'type' => 'text'
            ],
            'value' => $tags
        ]);

        $form->buildForm();

        $this->assertEquals('c++', $form->tags_v1->getChildren()[0]->getValue());
        $this->assertEquals('c#', $form->tags_v1->getChildren()[1]->getValue());

        $this->assertEquals('c++', $form->tags_v2->getChildren()[0]->getValue());
        $this->assertEquals('c#', $form->tags_v2->getChildren()[1]->getValue());
    }

    public function testBuildFormWithEmptyCollection()
    {
        $form = $this->createForm(TestForm::class);

        $form->add('tags', 'collection', [
            'property' => 'name',
            'child_attr' => [
                'type' => 'text'
            ]
        ]);

        $form->buildForm();
        $this->assertEmpty($form->get('tags')->getChildren());

        $tags = collect([
            ['id' => 1, 'name' => 'c++'],
            ['id' => 2, 'name' => 'c#']
        ]);

        $form->get('tags')->setValue($tags);
        $this->assertNotEmpty($form->get('tags')->getChildren());

        $this->assertEquals('c++', $form->tags->getChildren()[0]->getValue());
        $this->assertEquals('c#', $form->tags->getChildren()[1]->getValue());
    }

    public function testBuildFormWithEmptyChildForm()
    {
        $form = $this->createForm(TestForm::class);
        $this->assertInstanceOf(\Illuminate\Http\Request::class, $form->getRequest());

        $child = $this->createForm(PollForm::class);
        $child->buildForm();

        $form->add('poll', 'child_form', ['class' => $child]);
        $form->buildForm();

        $this->assertNotEmpty($form->get('poll')->getForm()->getFields());
        $this->assertNotEmpty($form->get('poll')->getChildren());
        $this->assertEquals($form->get('poll')->getForm()->getFields(), $form->get('poll')->getChildren());

        $this->assertInstanceOf(\Illuminate\Http\Request::class, $form->get('poll')->getRequest());
        $this->assertEquals('poll[title]', $form->get('poll')->get('title')->getName());
        $this->assertEquals('poll[items]', $form->get('poll')->get('items')->getName());
    }

    public function testBuildFormWithChildFormAndFillUpWithArray()
    {
        $faker = Faker\Factory::create();
        $value = ['title' => $title = $faker->text(50), 'items' => $items = "Answer 1\nAnswer 2"];

        $this->fillUpChildFormWithData($value);
    }

    public function testBuildFormWithChildFormAndFillUpWithCollection()
    {
        $faker = Faker\Factory::create();
        $value = collect(['title' => $title = $faker->text(50), 'items' => $items = "Answer 1\nAnswer 2"]);

        $this->fillUpChildFormWithData($value);
    }

    private function fillUpChildFormWithData($value)
    {
        $form = $this->createForm(TestForm::class);

        $child = $this->createForm(PollForm::class);
        $child->buildForm();

        $form->add('poll', 'child_form', [
            'class' => $child,
            'value' => $value
        ]);
        $form->buildForm();

        $this->assertEquals($value['title'], $form->get('poll')->getForm()->get('title')->getValue());
        $this->assertEquals($value['title'], $form->get('poll')->get('title')->getValue());
        $this->assertEquals($value['items'], $form->get('poll')->get('items')->getValue());
    }

    public function testBuildFormWithObjectValues()
    {
        $fake = Factory::create();

        $data = (object) [
            'name' => $fake->name,
            'email' => $fake->email,
            'bio' => $fake->text,
            'group_id' => 2
        ];

        // wypelnienie danymi
        $this->fillWithData($data);
    }

    public function testBuildFormWithArrayValues()
    {
        $fake = Factory::create();

        $data = [
            'name' => $fake->name,
            'email' => $fake->email,
            'bio' => $fake->text,
            'group_id' => 2
        ];

        $this->fillWithData($data);
    }

    public function testBuildFormAndFillUpWithCollection()
    {
        $faker = Faker\Factory::create();

        $data = collect([
            'name' => $faker->name,
            'email' => $faker->email,
            'bio' => $faker->text(),
            'group_id' => 1
        ]);

        $form = $this->createForm(SampleForm::class, $data);
        $form->buildForm();

        $this->assertValue('name', $data['name'], $form);
        $this->assertValue('email', $data['email'], $form);
        $this->assertValue('bio', $data['bio'], $form);
        $this->assertValue('group_id', $data['group_id'], $form);
    }

    private function fillWithData($data)
    {
        // utworzenie instancji klasy
        $form = $this->createForm(SampleForm::class);
        // utworzenie pol i wypelnienie ich danymi
        $form->buildForm();

        // wypelnienie danymi
        $form->setData($data);

        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Text::class, $form->get('name'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Text::class, $form->get('email'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Textarea::class, $form->get('bio'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Select::class, $form->get('group_id'));

        $data = (array) $data;

        $this->assertValue('name', $data['name'], $form);
        $this->assertValue('email', $data['email'], $form);
        $this->assertValue('bio', $data['bio'], $form);
        $this->assertValue('group_id', $data['group_id'], $form);
    }

    private function assertValue($key, $value, $form)
    {
        $this->assertEquals($value, $form->get($key)->getValue());
        $this->assertEquals($value, $form->$key->getValue());
        $this->assertEquals($value, $form->getField($key)->getValue());
    }

    private function createForm($class, $data = null, array $options = [])
    {
        $form = new $class();
        $form->setContainer(app())
            ->setRedirector(app(Redirector::class))
            ->setRequest($this->getRequest())
            ->setData($data)
            ->setOptions($options);

        return $form;
    }

    private function getRequest()
    {
        $request = app()->make('request');
        $request->setSession(app('session')->driver('array'));

        return $request;
    }
}
