<h1>Edit Project</h1>

<form action="/SkillXchange/public/ProjectController/update/<?= $data['project']->id ?>" method="POST">

    <input type="text" name="name" value="<?= $data['project']->name ?>">

    <textarea name="description"><?= $data['project']->description ?></textarea>

    <input type="text" name="required_skills" value="<?= $data['project']->required_skills ?>">

    <input type="number" name="max_members" value="<?= $data['project']->max_members ?>">

    <input type="date" name="start_date" value="<?= $data['project']->start_date ?>">
    <input type="date" name="end_date" value="<?= $data['project']->end_date ?>">

    <button type="submit">Update</button>

</form>
