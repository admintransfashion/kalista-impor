import json
import os

datadikirim = os.getenv('data')

print('ini test python script')
print('---------------------')
print('variable yang dikirim')

data = json.loads(datadikirim)
print(data)

