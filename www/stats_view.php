
<div class="container mt-3" style="max-width:800px">

    <div class="row">
        <div class="col">
            <h3>View counts</h3>
            <p>Basic analytics for this site.</p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Views</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats as $page => $views): ?>
                        <tr>
                            <td><?php echo $page; ?></td>
                            <td><?php echo $views; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>