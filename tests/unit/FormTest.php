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

    private function createForm($class)
    {
        $request = app()->make('request');
        $request->setSession(app('session')->driver('array'));

        $form = new $class();
        $form->setContainer(app())
            ->setRedirector(app(Redirector::class))
            ->setRequest($request);

        return $form;
    }
}
