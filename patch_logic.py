import os
import re

dirs_to_check = [
    r'c:\laragon\www\ketahananPangan\app\Http\Controllers'
]

def patch_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    new_content = content
    
    # 1. Patch str_starts_with IDOR vulnerability
    pattern_str = r'!str_starts_with\(\(string\)\$([a-zA-Z0-9_]+)->id_tingkat, \(string\)\$scope\)'
    def repl_str(m):
        var_name = m.group(1)
        return f'((string)${var_name}->id_tingkat !== (string)$scope && !str_starts_with((string)${var_name}->id_tingkat, (string)$scope . \'.\'))'
    
    new_content = re.sub(pattern_str, repl_str, new_content)

    # 2. Patch closure query builder ->where($column, 'LIKE', $scope . '%')
    pattern_like_1 = r'return \$query->where\(\$column, \'LIKE\', \$scope \. \'%\'\);'
    repl_like_1 = r'return $query->where(function($q) use ($column, $scope) { $q->where($column, $scope)->orWhere($column, \'LIKE\', $scope . \'.%\'); });'
    new_content = re.sub(pattern_like_1, repl_like_1, new_content)

    # 3. Patch specific ->where('id_tingkat', 'LIKE', $filters['resor'] . '%');
    pattern_like_2 = r'->where\(\'id_tingkat\', \'LIKE\', \$filters\[\'resor\'\] \. \'%\'\)'
    repl_like_2 = r'->where(function($q) use ($filters) { $q->where(\'id_tingkat\', $filters[\'resor\'])->orWhere(\'id_tingkat\', \'LIKE\', $filters[\'resor\'] . \'.%\'); })'
    new_content = re.sub(pattern_like_2, repl_like_2, new_content)

    # 4. Patch $query->where($column, 'LIKE', $polresPrefix . '%');
    pattern_like_3 = r'->where\(\$column, \'LIKE\', \$polresPrefix \. \'%\'\)'
    repl_like_3 = r'->where(function($q) use ($column, $polresPrefix) { $q->where($column, $polresPrefix)->orWhere($column, \'LIKE\', $polresPrefix . \'.%\'); })'
    new_content = re.sub(pattern_like_3, repl_like_3, new_content)
    
    # 5. Patch $yearDetect->where('lahan.id_tingkat', 'LIKE', $scope . '%');
    pattern_like_4 = r'->where\(\'lahan\.id_tingkat\', \'LIKE\', \$scope \. \'%\'\)'
    repl_like_4 = r'->where(function($q) use ($scope) { $q->where(\'lahan.id_tingkat\', $scope)->orWhere(\'lahan.id_tingkat\', \'LIKE\', $scope . \'.%\'); })'
    new_content = re.sub(pattern_like_4, repl_like_4, new_content)

    # 6. Patch orWhereRaw("? LIKE CONCAT(id_tingkat, '%')", [$scope])
    pattern_like_5 = r'->orWhereRaw\("\? LIKE CONCAT\(id_tingkat, \'%\'\)", \[\$scope\]\)'
    repl_like_5 = r'->orWhereRaw("? = id_tingkat OR ? LIKE CONCAT(id_tingkat, \'.%\')", [$scope, $scope])'
    new_content = re.sub(pattern_like_5, repl_like_5, new_content)

    # 7. Patch ->where('id_tingkat', 'LIKE', $scope . '%') (without return)
    pattern_like_6 = r'->where\(\'id_tingkat\', \'LIKE\', \$scope \. \'%\'\)'
    repl_like_6 = r'->where(function($q) use ($scope) { $q->where(\'id_tingkat\', $scope)->orWhere(\'id_tingkat\', \'LIKE\', $scope . \'.%\'); })'
    new_content = re.sub(pattern_like_6, repl_like_6, new_content)
    
    # 8. Patch ->where('id_tingkat', 'LIKE', $polresPrefix . '%') (without return)
    pattern_like_7 = r'->where\(\'id_tingkat\', \'LIKE\', \$polresPrefix \. \'%\'\)'
    repl_like_7 = r'->where(function($q) use ($polresPrefix) { $q->where(\'id_tingkat\', $polresPrefix)->orWhere(\'id_tingkat\', \'LIKE\', $polresPrefix . \'.%\'); })'
    new_content = re.sub(pattern_like_7, repl_like_7, new_content)

    if new_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f'Patched {filepath}')

for root, _, files in os.walk(dirs_to_check[0]):
    for file in files:
        if file.endswith('.php'):
            patch_file(os.path.join(root, file))
