<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>DataTables ‑ Demo Page</title>

  <!-- Bootstrap 5.3.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- DataTables + Bootstrap 5 skin -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <style>
    body { font-family: "Inter", sans-serif; }
  </style>
</head>
<body class="bg-light p-4">

  <div class="container-lg">
    <h1 class="mb-4 text-center">DataTables Test Page</h1>

    <table id="postsTable" class="table table-striped table-bordered" style="width:100%">
      <thead class="table-dark text-center align-middle">
        <tr>
          <th>Title</th>
          <th>Content</th>
          <th>Category</th>
          <th>Author</th>
          <th>Status</th>
          <th>Created At</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>First Demo Post</td>
          <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
          <td>Announcement</td>
          <td>Alice</td>
          <td><span class="badge bg-success">Published</span></td>
          <td>2025‑07‑04 09:15 AM</td>
        </tr>
        <tr>
          <td>Second Demo Post</td>
          <td>Vivamus luctus urna sed urna ultricies ac tempor dui sagittis.</td>
          <td>News</td>
          <td>Bob</td>
          <td><span class="badge bg-secondary">Draft</span></td>
          <td>2025‑07‑02 02:30 PM</td>
        </tr>
        <tr>
          <td>Third Demo Post</td>
          <td>Integer vitae libero ac risus egestas placerat.</td>
          <td>Event</td>
          <td>Charlie</td>
          <td><span class="badge bg-success">Published</span></td>
          <td>2025‑06‑28 11:05 AM</td>
        </tr>
        <!-- 📝 Add as many rows as you like to test scrolling / paging -->
      </tbody>
    </table>
  </div>

  <!-- jQuery (required for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- Bootstrap Bundle (Popper included) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables core + Bootstrap 5 adapter -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(function () {
      $('#postsTable').DataTable({
        pageLength: 10,
        order: [[0, 'asc']]
      });
    });
  </script>
</body>
</html>
